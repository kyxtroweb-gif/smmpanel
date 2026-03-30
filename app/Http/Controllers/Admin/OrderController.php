<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Service;
use App\Models\Dripfeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of orders.
     */
    public function getIndex(Request $request)
    {
        $query = Order::with(['user:id,name,email', 'service:id,name']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('link', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by service
        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $statuses = ['pending', 'processing', 'inprogress', 'completed', 'cancelled', 'refunded', 'partial'];
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $services = Service::orderBy('name')->get(['id', 'name']);

        return view('admin.orders.index', compact('orders', 'statuses', 'users', 'services'));
    }

    /**
     * Display order details.
     */
    public function getView(int $id)
    {
        $order = Order::with(['user.profile', 'service', 'provider'])
            ->findOrFail($id);

        $activities = [];
        // If you have an order activity model, fetch it here
        // $activities = OrderActivity::where('order_id', $id)->get();

        return view('admin.orders.view', compact('order', 'activities'));
    }

    /**
     * Cancel an order and refund balance.
     */
    public function postCancel(int $id)
    {
        $order = Order::findOrFail($id);

        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'Only pending or processing orders can be cancelled.');
        }

        DB::beginTransaction();

        try {
            $user = $order->user;
            $refundAmount = $order->total;

            // Refund balance to user
            $user->profile->balance += $refundAmount;
            $user->profile->save();

            // Update order status
            $order->status = 'cancelled';
            $order->save();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Cancelled order #{$order->id} and refunded {$refundAmount} to {$user->email}");

            DB::commit();

            return back()->with('success', "Order cancelled and {$refundAmount} refunded to user.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    /**
     * Refund an order.
     */
    public function postRefund(int $id)
    {
        $order = Order::findOrFail($id);

        if ($order->status === 'refunded') {
            return back()->with('error', 'Order is already refunded.');
        }

        if ($order->status === 'completed' && !$order->service->refill) {
            return back()->with('error', 'This order is not eligible for refund (no refill available).');
        }

        DB::beginTransaction();

        try {
            $user = $order->user;
            $refundAmount = $order->total;

            // Refund balance to user
            $user->profile->balance += $refundAmount;
            $user->profile->save();

            // Update order status
            $order->status = 'refunded';
            $order->save();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Refunded order #{$order->id} - Amount: {$refundAmount} - User: {$user->email}");

            DB::commit();

            return back()->with('success', "Order refunded. {$refundAmount} returned to user balance.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to refund order: ' . $e->getMessage());
        }
    }

    /**
     * Process a partial refund.
     */
    public function postPartial(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01', 'max:' . $order->total],
            'note' => ['required', 'string', 'max:500'],
        ]);

        if ($order->status === 'refunded' || $order->status === 'cancelled') {
            return back()->with('error', 'Order is already refunded or cancelled.');
        }

        DB::beginTransaction();

        try {
            $user = $order->user;
            $refundAmount = (float) $request->refund_amount;

            // Refund partial amount to user
            $user->profile->balance += $refundAmount;
            $user->profile->save();

            // Update order
            $order->total = $order->total - $refundAmount;
            $order->status = 'partial';
            $order->notes = ($order->notes ? $order->notes . "\n" : '') . "Partial refund: {$refundAmount}. Note: {$request->note}";
            $order->save();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Partial refund on order #{$order->id}: {$refundAmount}. Note: {$request->note}");

            DB::commit();

            return back()->with('success', "Partial refund of {$refundAmount} processed.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process partial refund: ' . $e->getMessage());
        }
    }

    /**
     * Update order status manually.
     */
    public function postUpdateStatus(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:pending,processing,inprogress,completed,cancelled,refunded,partial'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        DB::beginTransaction();

        try {
            $order->status = $newStatus;
            if ($request->has('note')) {
                $order->notes = ($order->notes ? $order->notes . "\n" : '') . $request->note;
            }
            $order->save();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Order #{$order->id} status changed from {$oldStatus} to {$newStatus}");

            DB::commit();

            return back()->with('success', "Order status updated to {$newStatus}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    /**
     * Update order start count.
     */
    public function postUpdateStartCount(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'start_count' => ['required', 'integer', 'min:0'],
        ]);

        $order->start_count = $request->start_count;
        $order->save();

        activity()
            ->causedBy(auth()->user())
            ->log("Order #{$order->id} start count updated to {$request->start_count}");

        return back()->with('success', 'Start count updated.');
    }

    /**
     * Export orders to CSV.
     */
    public function getExport(Request $request)
    {
        $query = Order::with(['user:id,name,email', 'service:id,name']);

        // Apply same filters as index
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        $csvData[] = ['ID', 'User', 'Email', 'Service', 'Link', 'Quantity', 'Total', 'Status', 'Start Count', 'Remains', 'Created At', 'Updated At'];

        foreach ($orders as $order) {
            $csvData[] = [
                $order->id,
                $order->user->name ?? 'N/A',
                $order->user->email ?? 'N/A',
                $order->service->name ?? 'N/A',
                $order->link,
                $order->quantity,
                number_format($order->total, 2),
                $order->status,
                $order->start_count ?? 0,
                $order->remains ?? 0,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        $filename = 'orders_export_' . date('Y-m-d_His') . '.csv';

        $handle = fopen('php://temp', 'r+');

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get order statistics.
     */
    public function getStats(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        $stats = Order::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalOrders = $stats->sum('count');
        $totalRevenue = $stats->sum('total');

        return response()->json([
            'stats' => $stats,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
        ]);
    }

    /**
     * Bulk cancel orders.
     */
    public function postBulkCancel(Request $request)
    {
        $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
        ]);

        $orderIds = $request->order_ids;

        DB::beginTransaction();

        try {
            $orders = Order::whereIn('id', $orderIds)
                ->whereIn('status', ['pending', 'processing'])
                ->get();

            $cancelledCount = 0;
            $totalRefund = 0;

            foreach ($orders as $order) {
                $user = $order->user;
                $user->profile->balance += $order->total;
                $user->profile->save();

                $order->status = 'cancelled';
                $order->save();

                $totalRefund += $order->total;
                $cancelledCount++;
            }

            activity()
                ->causedBy(auth()->user())
                ->log("Bulk cancelled {$cancelledCount} orders. Total refund: {$totalRefund}");

            DB::commit();

            return back()->with('success', "{$cancelledCount} orders cancelled. Total refund: {$totalRefund}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to bulk cancel orders: ' . $e->getMessage());
        }
    }
}
