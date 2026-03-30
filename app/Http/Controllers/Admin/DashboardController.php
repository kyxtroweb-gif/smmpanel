<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display the admin dashboard.
     */
    public function getIndex()
    {
        $stats = $this->getDashboardStats();
        $recentOrders = $this->getRecentOrders();
        $recentPayments = $this->getRecentPayments();
        $topServices = $this->getTopServices();
        $monthlyStats = $this->getMonthlyStats();

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'recentPayments',
            'topServices',
            'monthlyStats'
        ));
    }

    /**
     * Get dashboard statistics.
     */
    protected function getDashboardStats()
    {
        $today = Carbon::today();

        return [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', $today)->count(),
            'total_orders' => Order::count(),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_services' => Service::count(),
            'active_services' => Service::where('is_active', true)->count(),
            'today_revenue' => Payment::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('amount'),
            'monthly_revenue' => Payment::whereMonth('created_at', now()->month)
                ->where('status', 'completed')
                ->sum('amount'),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'pending_tickets' => Ticket::whereIn('status', ['open', 'pending'])->count(),
            'closed_tickets' => Ticket::where('status', 'closed')->count(),
        ];
    }

    /**
     * Get recent orders.
     */
    protected function getRecentOrders($limit = 10)
    {
        return Order::with(['user:id,name,email', 'service:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent payments.
     */
    protected function getRecentPayments($limit = 10)
    {
        return Payment::with(['user:id,name,email', 'paymentMethod:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top performing services.
     */
    protected function getTopServices($limit = 10)
    {
        return Order::select('service_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total) as total_revenue'))
            ->with('service:id,name')
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->orderBy('order_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get monthly statistics for charts.
     */
    protected function getMonthlyStats()
    {
        $months = [];
        $ordersData = [];
        $revenueData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $ordersData[] = Order::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();

            $revenueData[] = Payment::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->where('status', 'completed')
                ->sum('amount');
        }

        return [
            'months' => $months,
            'orders' => $ordersData,
            'revenue' => $revenueData,
        ];
    }

    /**
     * Get order statistics by status.
     */
    public function getOrderStats()
    {
        $stats = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'pending' => $stats['pending'] ?? 0,
            'processing' => $stats['processing'] ?? 0,
            'inprogress' => $stats['inprogress'] ?? 0,
            'completed' => $stats['completed'] ?? 0,
            'cancelled' => $stats['cancelled'] ?? 0,
            'refunded' => $stats['refunded'] ?? 0,
            'partial' => $stats['partial'] ?? 0,
        ]);
    }

    /**
     * Get real-time dashboard data (AJAX).
     */
    public function getRealtimeData()
    {
        return response()->json([
            'orders_today' => Order::whereDate('created_at', Carbon::today())->count(),
            'pending_tickets' => Ticket::whereIn('status', ['open', 'pending'])->count(),
            'today_revenue' => Payment::whereDate('created_at', Carbon::today())
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
        ]);
    }
}
