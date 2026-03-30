<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Refill;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard with stats.
     */
    public function getIndex(Request $request): View
    {
        $user = auth()->user();

        // Calculate user-specific stats
        $stats = [
            'balance' => $user->balance,
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'pending_orders' => Order::where('user_id', $user->id)->whereIn('status', ['pending', 'in_progress'])->count(),
            'completed_orders' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'in_progress', 'partial'])
                ->sum('price'),
            'total_refill_requests' => Refill::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
        ];

        // Recent orders
        $recentOrders = Order::with('service')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Monthly spending chart data
        $monthlySpending = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->whereIn('status', ['completed', 'in_progress', 'partial'])
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(price) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Order status breakdown
        $orderStatusBreakdown = Order::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('user.dashboard', compact(
            'stats',
            'recentOrders',
            'monthlySpending',
            'orderStatusBreakdown'
        ));
    }

    /**
     * Display the profile edit form.
     */
    public function getProfile(): View
    {
        $user = auth()->user();
        $timezones = timezone_identifiers_list();
        $languages = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'ar' => 'Arabic',
            'zh' => 'Chinese',
            'hi' => 'Hindi',
            'tr' => 'Turkish',
        ];

        return view('user.profile', compact('user', 'timezones', 'languages'));
    }

    /**
     * Update user profile.
     */
    public function postProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'timezone' => 'required|string|timezone',
            'language' => 'required|string|in:en,es,fr,de,pt,ru,ar,zh,hi,tr',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }

        // Update or create user profile
        $profile = $user->profile;
        if (!$profile) {
            $profile = new \App\Models\UserProfile(['user_id' => $user->id]);
        }
        $profile->timezone = $validated['timezone'];
        $profile->language = $validated['language'];
        $profile->save();

        // Update user settings
        $user->settings = array_merge($user->settings ?? [], [
            'timezone' => $validated['timezone'],
            'language' => $validated['language'],
        ]);
        $user->save();

        // Change password if provided
        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
            $user->save();
        }

        return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Change user password (dedicated endpoint).
     */
    public function postChangePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->back()
                ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']))
                ->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        // Invalidate other sessions for security
        // Note: In Laravel 11, you might need to implement this manually or use session invalidation

        return redirect()->route('user.profile')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Display API key management page.
     */
    public function getApiKey(): View
    {
        $user = auth()->user();

        // Generate API key if not exists
        if (empty($user->api_key)) {
            $user->api_key = Str::random(64);
            $user->save();
        }

        return view('user.api-key', compact('user'));
    }

    /**
     * Regenerate API key.
     */
    public function postRegenerateApiKey(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'confirm' => 'required|string|in:REGENERATE',
        ]);

        // Generate new API key
        $user->api_key = Str::random(64);
        $user->api_key_generated_at = now();
        $user->save();

        return redirect()->route('user.api-key')
            ->with('success', 'API key regenerated successfully. Please update your applications.');
    }

    /**
     * Display account settings.
     */
    public function getSettings(): View
    {
        $user = auth()->user();
        return view('user.settings', compact('user'));
    }

    /**
     * Update notification settings.
     */
    public function postNotificationSettings(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'order_updates' => 'boolean',
            'promotional_emails' => 'boolean',
            'payment_alerts' => 'boolean',
        ]);

        $user->notification_settings = [
            'email' => $validated['email_notifications'] ?? false,
            'order_updates' => $validated['order_updates'] ?? false,
            'promotional' => $validated['promotional_emails'] ?? false,
            'payment_alerts' => $validated['payment_alerts'] ?? false,
        ];
        $user->save();

        return redirect()->route('user.settings')
            ->with('success', 'Notification settings updated.');
    }

    /**
     * Display account statistics page.
     */
    public function getStatistics(): View
    {
        $user = auth()->user();

        $statistics = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'completed_orders' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'failed_orders' => Order::where('user_id', $user->id)->where('status', 'failed')->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'in_progress', 'partial'])
                ->sum('price'),
            'average_order_value' => Order::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'in_progress', 'partial'])
                ->avg('price') ?? 0,
            'total_deposits' => \App\Models\Payment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'total_refunds' => Order::where('user_id', $user->id)
                ->where('status', 'refunded')
                ->sum('price'),
        ];

        // Orders by service category
        $ordersByCategory = Order::where('orders.user_id', $user->id)
            ->join('services', 'orders.service_id', '=', 'services.id')
            ->join('categories', 'services.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, COUNT(*) as count, SUM(orders.price) as total')
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return view('user.statistics', compact('statistics', 'ordersByCategory'));
    }
}
