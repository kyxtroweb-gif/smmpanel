<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display profit report.
     */
    public function getProfit(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->toDateString());

        // Get orders grouped by date and service
        $profitByDate = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(cost) as total_cost')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->where('status', 'completed')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        // Get orders grouped by service
        $profitByService = Order::select(
                'service_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(cost) as total_cost')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->where('status', 'completed')
            ->groupBy('service_id')
            ->with('service:id,name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Calculate totals
        $totals = [
            'revenue' => $profitByDate->sum('total_revenue'),
            'cost' => $profitByDate->sum('total_cost'),
            'profit' => $profitByDate->sum('total_revenue') - $profitByDate->sum('total_cost'),
            'orders' => $profitByDate->sum('orders_count'),
        ];

        // Summary by status
        $statusSummary = Order::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('admin.reports.profit', compact(
            'profitByDate',
            'profitByService',
            'totals',
            'statusSummary',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display detailed order report.
     */
    public function getOrders(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->toDateString());
        $status = $request->get('status', 'all');

        $query = Order::with(['user:id,name,email', 'service:id,name'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50))
            ->withQueryString();

        $statuses = ['pending', 'processing', 'inprogress', 'completed', 'cancelled', 'refunded', 'partial'];

        // Summary statistics
        $summary = [
            'total_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count(),
            'completed_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'completed')->count(),
            'total_revenue' => Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'completed')->sum('total'),
            'average_order_value' => Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'completed')->avg('total') ?? 0,
        ];

        return view('admin.reports.orders', compact(
            'orders',
            'statuses',
            'summary',
            'dateFrom',
            'dateTo',
            'status'
        ));
    }

    /**
     * Display payment report.
     */
    public function getPayments(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->toDateString());
        $status = $request->get('status', 'all');

        $query = Payment::with(['user:id,name,email', 'paymentMethod:id,name'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50))
            ->withQueryString();

        $statuses = ['pending', 'completed', 'rejected', 'refunded'];

        // Summary statistics
        $summary = [
            'total_payments' => Payment::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count(),
            'completed_payments' => Payment::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'completed')->count(),
            'total_deposited' => Payment::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'completed')->sum('amount'),
            'pending_amount' => Payment::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'pending')->sum('amount'),
        ];

        // Deposits by payment method
        $byMethod = Payment::select('payment_method_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->where('status', 'completed')
            ->groupBy('payment_method_id')
            ->with('paymentMethod:id,name')
            ->get();

        return view('admin.reports.payments', compact(
            'payments',
            'statuses',
            'summary',
            'byMethod',
            'dateFrom',
            'dateTo',
            'status'
        ));
    }

    /**
     * Display activity log.
     */
    public function getActivity(Request $request)
    {
        $query = ActivityLog::with(['causer:id,name,email']);

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by action type
        if ($request->has('action') && $request->action !== 'all') {
            $query->where('description', 'like', '%' . $request->action . '%');
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50))
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.reports.activity', compact('activities', 'users'));
    }

    /**
     * Display user activity report.
     */
    public function getUsers(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->toDateString());

        $users = User::select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('COALESCE(SUM(orders.total), 0) as total_spent'),
                DB::raw('COUNT(DISTINCT payments.id) as total_deposits'),
                DB::raw('COALESCE(SUM(payments.amount), 0) as total_deposited')
            )
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->leftJoin('payments', function ($join) use ($dateFrom, $dateTo) {
                $join->on('users.id', '=', 'payments.user_id')
                    ->where('payments.status', 'completed')
                    ->whereBetween('payments.created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })
            ->whereBetween('users.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_spent', 'desc')
            ->paginate($request->get('per_page', 50));

        // Summary
        $summary = [
            'total_users' => User::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count(),
            'active_users' => User::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->whereHas('orders', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
                })->count(),
            'total_revenue' => Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'completed')->sum('total'),
        ];

        return view('admin.reports.users', compact('users', 'summary', 'dateFrom', 'dateTo'));
    }

    /**
     * Display service performance report.
     */
    public function getServices(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->toDateString());

        $services = Service::select(
                'services.id',
                'services.name',
                'categories.name as category_name',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.quantity) as total_quantity'),
                DB::raw('SUM(orders.total) as total_revenue'),
                DB::raw('SUM(orders.cost) as total_cost'),
                DB::raw('AVG(orders.total) as average_order')
            )
            ->leftJoin('orders', 'services.id', '=', 'orders.service_id')
            ->leftJoin('categories', 'services.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->where('orders.status', 'completed')
            ->groupBy('services.id', 'services.name', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->paginate($request->get('per_page', 50));

        // Summary
        $summary = [
            'total_services' => Service::count(),
            'active_services' => Service::where('is_active', true)->count(),
            'total_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'completed')->count(),
            'total_revenue' => Order::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'completed')->sum('total'),
        ];

        return view('admin.reports.services', compact('services', 'summary', 'dateFrom', 'dateTo'));
    }

    /**
     * Display ticket report.
     */
    public function getTickets(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->toDateString());

        $tickets = Ticket::with(['user:id,name,email', 'assignee:id,name'])
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        // Summary by status
        $statusSummary = Ticket::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Summary by priority
        $prioritySummary = Ticket::select('priority', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->groupBy('priority')
            ->get()
            ->keyBy('priority');

        // Average response time
        $avgResponseTime = TicketReply::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, ticket_replies.created_at)) as avg_hours'))
            ->join('tickets', 'ticket_replies.ticket_id', '=', 'tickets.id')
            ->first();

        $summary = [
            'total_tickets' => Ticket::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count(),
            'open_tickets' => Ticket::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'open')->count(),
            'closed_tickets' => Ticket::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->where('status', 'closed')->count(),
            'avg_response_hours' => round($avgResponseTime->avg_hours ?? 0, 1),
        ];

        return view('admin.reports.tickets', compact(
            'tickets',
            'statusSummary',
            'prioritySummary',
            'summary',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export profit report.
     */
    public function getExportProfit(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->toDateString());

        $profitByService = Order::select(
                'service_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(cost) as total_cost')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->where('status', 'completed')
            ->groupBy('service_id')
            ->with('service:id,name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $csvData = [];
        $csvData[] = ['Service ID', 'Service Name', 'Orders', 'Revenue', 'Cost', 'Profit'];

        foreach ($profitByService as $item) {
            $profit = $item->total_revenue - $item->total_cost;
            $csvData[] = [
                $item->service_id ?? 'N/A',
                $item->service->name ?? 'N/A',
                $item->orders_count,
                number_format($item->total_revenue, 2),
                number_format($item->total_cost, 2),
                number_format($profit, 2),
            ];
        }

        $filename = 'profit_report_' . $dateFrom . '_to_' . $dateTo . '.csv';

        return $this->downloadCsv($csvData, $filename);
    }

    /**
     * Generate CSV download.
     */
    protected function downloadCsv(array $data, string $filename)
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
