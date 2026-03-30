<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\Order;
use App\Models\Dripfeed;
use App\Models\Subscription;
use App\Models\Refill;
use App\Services\OrderDispatchService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected OrderDispatchService $orderDispatch;

    public function __construct(OrderDispatchService $orderDispatch)
    {
        $this->orderDispatch = $orderDispatch;
    }

    /**
     * Display paginated order history with filters.
     */
    public function getIndex(Request $request): View
    {
        $user = auth()->user();

        $query = Order::with('service.category')
            ->where('user_id', $user->id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhere('link', 'like', '%' . $search . '%');
            });
        }

        // Order by newest first
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        // Get status counts for filter tabs
        $statusCounts = Order::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Get user's services for filter dropdown
        $userServices = Order::where('user_id', $user->id)
            ->join('services', 'orders.service_id', '=', 'services.id')
            ->select('services.id', 'services.name')
            ->distinct()
            ->orderBy('services.name')
            ->get();

        return view('user.orders.index', compact('orders', 'statusCounts', 'userServices'));
    }

    /**
     * Display order placement form.
     */
    public function getNew(Request $request): View
    {
        $categories = Category::where('status', 'active')
            ->with(['services' => function ($query) {
                $query->where('status', 'active')->orderBy('price', 'asc');
            }])
            ->orderBy('sort_order')
            ->get();

        $selectedCategory = $request->get('category');

        // Get services for selected category if specified
        $services = collect();
        if ($selectedCategory) {
            $services = Service::where('category_id', $selectedCategory)
                ->where('status', 'active')
                ->orderBy('price', 'asc')
                ->get();
        }

        return view('user.orders.create', compact('categories', 'services', 'selectedCategory'));
    }

    /**
     * Get services for a category (AJAX).
     */
    public function getServicesByCategory(int $categoryId): \Illuminate\Http\JsonResponse
    {
        $services = Service::where('category_id', $categoryId)
            ->where('status', 'active')
            ->orderBy('price', 'asc')
            ->get()
            ->map(function ($service) {
                $user = auth()->user();
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->getPriceForUser($user),
                    'min_quantity' => $service->min_quantity,
                    'max_quantity' => $service->max_quantity,
                    'description' => $service->description,
                    'average_time' => $service->average_time,
                    'dripfeed' => $service->dripfeed,
                    'refill' => $service->refill,
                ];
            });

        return response()->json($services);
    }

    /**
     * Get service details (AJAX).
     */
    public function getServiceDetails(int $id): \Illuminate\Http\JsonResponse
    {
        $service = Service::with('category', 'provider')
            ->where('status', 'active')
            ->findOrFail($id);

        $user = auth()->user();
        $price = $service->getPriceForUser($user);

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'category' => $service->category->name,
            'provider' => $service->provider->name ?? 'Direct',
            'price' => $price,
            'min_quantity' => $service->min_quantity,
            'max_quantity' => $service->max_quantity,
            'description' => $service->description,
            'average_time' => $service->average_time,
            'dripfeed' => $service->dripfeed,
            'refill' => $service->refill,
            'supports_partial' => $service->supports_partial,
            'cancel_available' => $service->cancel_available,
        ]);
    }

    /**
     * Calculate order price (AJAX).
     */
    public function postCalculatePrice(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $user = auth()->user();
        $price = $service->getPriceForUser($user);
        $total = $price * $validated['quantity'];

        // Check if quantity is within limits
        if ($validated['quantity'] < $service->min_quantity) {
            return response()->json([
                'error' => "Minimum quantity is {$service->min_quantity}",
            ], 422);
        }

        if ($validated['quantity'] > $service->max_quantity) {
            return response()->json([
                'error' => "Maximum quantity is {$service->max_quantity}",
            ], 422);
        }

        return response()->json([
            'price_per_item' => $price,
            'quantity' => $validated['quantity'],
            'total' => $total,
            'service_charge' => 0,
            'final_total' => $total,
        ]);
    }

    /**
     * Place a single order.
     */
    public function postStore(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'link' => 'required|url|max:500',
            'quantity' => 'required|integer|min:1',
            'comments' => 'nullable|string|max:500',
        ]);

        $service = Service::where('status', 'active')
            ->findOrFail($validated['service_id']);

        // Validate quantity limits
        if ($validated['quantity'] < $service->min_quantity) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => "Minimum quantity is {$service->min_quantity}"]);
        }

        if ($validated['quantity'] > $service->max_quantity) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => "Maximum quantity is {$service->max_quantity}"]);
        }

        // Calculate price
        $price = $service->getPriceForUser($user);
        $total = $price * $validated['quantity'];

        // Check user balance
        if ($user->balance < $total) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['balance' => 'Insufficient balance. Please deposit funds.']);
        }

        try {
            DB::beginTransaction();

            // Deduct balance
            $user->balance -= $total;
            $user->save();

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'provider_id' => $service->provider_id,
                'order_number' => Order::generateOrderNumber(),
                'link' => $validated['link'],
                'quantity' => $validated['quantity'],
                'price' => $price,
                'total' => $total,
                'status' => 'pending',
                'mode' => 'manual',
                'comments' => $validated['comments'] ?? null,
            ]);

            // Create transaction record
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'order',
                'amount' => -$total,
                'balance' => $user->balance,
                'reference' => 'order:' . $order->id,
                'description' => "Order #{$order->order_number} - {$service->name}",
            ]);

            DB::commit();

            // Dispatch to provider API
            $this->orderDispatch->dispatch($order);

            return redirect()->route('user.orders.view', $order->id)
                ->with('success', "Order #{$order->order_number} placed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();

            // Refund balance if something went wrong before dispatch
            if (isset($order)) {
                $user->balance += $total;
                $user->save();
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to place order: ' . $e->getMessage()]);
        }
    }

    /**
     * Place bulk orders.
     */
    public function postBulk(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'links' => 'required|string',
        ]);

        $service = Service::where('status', 'active')
            ->findOrFail($validated['service_id']);

        // Parse links (one per line)
        $links = array_filter(array_map('trim', explode("\n", $validated['links'])));
        $links = array_slice($links, 0, 100); // Limit to 100 orders

        if (empty($links)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['links' => 'Please enter at least one link.']);
        }

        // Calculate total price
        $price = $service->getPriceForUser($user);
        $total = $price * count($links);

        // Check user balance
        if ($user->balance < $total) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['balance' => 'Insufficient balance for all orders.']);
        }

        $orders = [];
        $failedLinks = [];

        try {
            DB::beginTransaction();

            foreach ($links as $link) {
                // Validate URL
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    $failedLinks[] = $link;
                    continue;
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'provider_id' => $service->provider_id,
                    'order_number' => Order::generateOrderNumber(),
                    'link' => $link,
                    'quantity' => 1,
                    'price' => $price,
                    'total' => $price,
                    'status' => 'pending',
                    'mode' => 'bulk',
                ]);

                $orders[] = $order;

                // Dispatch to provider
                $this->orderDispatch->dispatch($order);
            }

            // Deduct total from balance
            $user->balance -= ($price * count($orders));
            $user->save();

            // Create transaction record
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'bulk_order',
                'amount' => -($price * count($orders)),
                'balance' => $user->balance,
                'reference' => 'bulk:' . implode(',', array_column($orders, 'id')),
                'description' => "Bulk Order ({$service->name}) - " . count($orders) . " orders",
            ]);

            DB::commit();

            $message = count($orders) . " order(s) placed successfully!";
            if (!empty($failedLinks)) {
                $message .= " " . count($failedLinks) . " link(s) failed validation.";
            }

            return redirect()->route('user.orders.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Bulk order failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Place drip-feed order.
     */
    public function postDripfeed(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'link' => 'required|url|max:500',
            'quantity' => 'required|integer|min:1',
            'runs' => 'required|integer|min:2|max:100',
            'interval' => 'required|integer|min:1|max:60',
        ]);

        $service = Service::where('status', 'active')
            ->where('dripfeed', true)
            ->findOrFail($validated['service_id']);

        if (!$service->dripfeed) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['service_id' => 'This service does not support drip-feed orders.']);
        }

        // Calculate total
        $price = $service->getPriceForUser($user);
        $totalQuantity = $validated['quantity'] * $validated['runs'];
        $total = $price * $totalQuantity;

        // Validate total quantity
        if ($totalQuantity < $service->min_quantity) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => 'Total quantity exceeds minimum limit.']);
        }

        if ($totalQuantity > $service->max_quantity) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => 'Total quantity exceeds maximum limit.']);
        }

        // Check user balance
        if ($user->balance < $total) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['balance' => 'Insufficient balance.']);
        }

        try {
            DB::beginTransaction();

            // Create parent order
            $order = Order::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'provider_id' => $service->provider_id,
                'order_number' => Order::generateOrderNumber(),
                'link' => $validated['link'],
                'quantity' => $totalQuantity,
                'price' => $price,
                'total' => $total,
                'status' => 'pending',
                'mode' => 'dripfeed',
            ]);

            // Create dripfeed record
            $dripfeed = Dripfeed::create([
                'order_id' => $order->id,
                'runs' => $validated['runs'],
                'interval' => $validated['interval'],
                'quantity_per_run' => $validated['quantity'],
                'total_quantity' => $totalQuantity,
                'remaining_runs' => $validated['runs'],
                'next_run_at' => now()->addMinutes($validated['interval']),
                'status' => 'active',
            ]);

            // Deduct balance
            $user->balance -= $total;
            $user->save();

            // Create transaction
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'dripfeed',
                'amount' => -$total,
                'balance' => $user->balance,
                'reference' => 'dripfeed:' . $dripfeed->id,
                'description' => "Drip-feed Order #{$order->order_number}",
            ]);

            DB::commit();

            return redirect()->route('user.orders.view', $order->id)
                ->with('success', "Drip-feed order placed! It will run {$validated['runs']} times every {$validated['interval']} minutes.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to place drip-feed order: ' . $e->getMessage()]);
        }
    }

    /**
     * Place subscription/auto order.
     */
    public function postSubscription(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'link' => 'required|url|max:500',
            'posts_count' => 'required|integer|min:1|max:1000',
            'interval' => 'required|integer|min:5|max:1440',
        ]);

        $service = Service::where('status', 'active')
            ->findOrFail($validated['service_id']);

        // Calculate approximate total (will be charged per post)
        $price = $service->getPriceForUser($user);
        $estimatedTotal = $price * $validated['posts_count'];

        // Check user balance
        if ($user->balance < $estimatedTotal) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['balance' => 'Insufficient balance.']);
        }

        try {
            DB::beginTransaction();

            // Create parent order
            $order = Order::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'provider_id' => $service->provider_id,
                'order_number' => Order::generateOrderNumber(),
                'link' => $validated['link'],
                'quantity' => $validated['posts_count'],
                'price' => $price,
                'total' => 0, // Will be updated as posts are sent
                'status' => 'pending',
                'mode' => 'subscription',
            ]);

            // Create subscription record
            $subscription = Subscription::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'service_id' => $service->id,
                'link' => $validated['link'],
                'posts_count' => $validated['posts_count'],
                'posts_sent' => 0,
                'interval' => $validated['interval'],
                'price_per_post' => $price,
                'total_spent' => 0,
                'status' => 'active',
                'last_run_at' => null,
                'next_run_at' => now()->addMinutes($validated['interval']),
            ]);

            // Deduct initial charge (first post estimate)
            $user->balance -= $price;
            $user->save();

            // Create transaction
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'subscription',
                'amount' => -$price,
                'balance' => $user->balance,
                'reference' => 'subscription:' . $subscription->id,
                'description' => "Subscription Order #{$order->order_number} (Initial)",
            ]);

            DB::commit();

            return redirect()->route('user.orders.view', $order->id)
                ->with('success', "Subscription started! {$validated['posts_count']} posts will be sent every {$validated['interval']} minutes.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Display order details.
     */
    public function getView(string $orderId): View
    {
        $user = auth()->user();

        $order = Order::with(['service.category', 'provider', 'dripfeed', 'subscription'])
            ->where('user_id', $user->id)
            ->findOrFail($orderId);

        // Get order history/status changes
        $statusHistory = $order->status_history ?? [];

        // Get refill requests for this order
        $refills = Refill::where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate progress
        $progress = 0;
        if ($order->status === 'completed') {
            $progress = 100;
        } elseif ($order->status === 'partial') {
            $delivered = $order->quantity - ($order->remains ?? 0);
            $progress = $order->quantity > 0 ? round(($delivered / $order->quantity) * 100, 2) : 0;
        }

        return view('user.orders.view', compact('order', 'refills', 'statusHistory', 'progress'));
    }

    /**
     * Cancel order if cancellable.
     */
    public function postCancel(string $orderId): RedirectResponse
    {
        $user = auth()->user();

        $order = Order::where('user_id', $user->id)
            ->findOrFail($orderId);

        // Check if order can be cancelled
        if (!$order->canBeCancelled()) {
            return redirect()->back()
                ->withErrors(['error' => 'This order cannot be cancelled.']);
        }

        try {
            DB::beginTransaction();

            $refundAmount = $order->total;

            // Update order status
            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->save();

            // Refund balance
            $user->balance += $refundAmount;
            $user->save();

            // Create refund transaction
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'balance' => $user->balance,
                'reference' => 'order_cancellation:' . $order->id,
                'description' => "Refund for cancelled Order #{$order->order_number}",
            ]);

            DB::commit();

            return redirect()->route('user.orders.view', $order->id)
                ->with('success', "Order #{$order->order_number} cancelled. Refund of \${$refundAmount} has been credited.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Failed to cancel order: ' . $e->getMessage()]);
        }
    }

    /**
     * Request refill for an order.
     */
    public function postRefill(string $orderId, Request $request): RedirectResponse
    {
        $user = auth()->user();

        $order = Order::where('user_id', $user->id)
            ->findOrFail($orderId);

        // Check if order supports refill
        if (!$order->service->refill) {
            return redirect()->back()
                ->withErrors(['error' => 'This service does not support refill.']);
        }

        // Check if order is eligible for refill
        if ($order->status !== 'completed' && $order->status !== 'partial') {
            return redirect()->back()
                ->withErrors(['error' => 'Only completed or partially delivered orders can be refilled.']);
        }

        // Check if there's already a pending refill request
        $existingRefill = Refill::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->first();

        if ($existingRefill) {
            return redirect()->back()
                ->withErrors(['error' => 'A refill request is already pending for this order.']);
        }

        try {
            DB::beginTransaction();

            // Create refill request
            $refill = Refill::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'quantity' => $order->quantity,
                'remaining_before' => $order->remains ?? 0,
                'status' => 'pending',
                'notes' => $request->get('notes'),
            ]);

            // Update order status
            $order->refill_count = ($order->refill_count ?? 0) + 1;
            $order->save();

            DB::commit();

            // Process refill with provider
            $this->orderDispatch->processRefill($order);

            return redirect()->route('user.orders.view', $order->id)
                ->with('success', 'Refill request submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Failed to submit refill request: ' . $e->getMessage()]);
        }
    }

    /**
     * Export orders to CSV.
     */
    public function getExport(Request $request)
    {
        $user = auth()->user();

        $query = Order::with('service.category')
            ->where('user_id', $user->id);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $csvContent = "Order ID,Service,Link,Quantity,Price,Total,Status,Created At\n";

        foreach ($orders as $order) {
            $csvContent .= sprintf(
                "%s,%s,%s,%d,%.4f,%.4f,%s,%s\n",
                $order->order_number,
                $order->service->name ?? 'N/A',
                $order->link,
                $order->quantity,
                $order->price,
                $order->total,
                $order->status,
                $order->created_at->format('Y-m-d H:i:s')
            );
        }

        $filename = 'orders_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(
            function () use ($csvContent) {
                echo $csvContent;
            },
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }
}
