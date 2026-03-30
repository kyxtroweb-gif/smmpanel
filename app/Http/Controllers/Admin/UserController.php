<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Excel;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of users.
     */
    public function getIndex(Request $request)
    {
        $query = User::with('profile');

        // Search by name or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        // Filter by role
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the create user form.
     */
    public function getCreate()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function postStore(Request $request)
    {
        $this->validator($request->all())->validate();

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
                'is_active' => $request->boolean('is_active'),
                'referral_code' => strtoupper(Str::random(8)),
            ]);

            // Create user profile
            UserProfile::create([
                'user_id' => $user->id,
                'balance' => $request->balance ?? 0.00,
                'total_spent' => 0.00,
                'total_orders' => 0,
                'country' => $request->country,
                'timezone' => $request->timezone ?? 'UTC',
                'email_notifications' => true,
            ]);

            // Store custom rates if provided
            if ($request->has('custom_rates') && is_array($request->custom_rates)) {
                foreach ($request->custom_rates as $serviceId => $rate) {
                    if ($rate !== null && $rate !== '') {
                        $user->customRates()->attach($serviceId, ['rate' => $rate]);
                    }
                }
            }

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Created user: {$user->email}");

            DB::commit();

            return redirect()->route('admin.users.edit', $user->id)
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Show the edit user form.
     */
    public function getEdit(int $id)
    {
        $user = User::with(['profile', 'customRates'])->findOrFail($id);

        $userOrders = Order::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $userPayments = Payment::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $userStats = [
            'total_orders' => Order::where('user_id', $id)->count(),
            'completed_orders' => Order::where('user_id', $id)->where('status', 'completed')->count(),
            'total_spent' => Payment::where('user_id', $id)->where('status', 'completed')->sum('amount'),
        ];

        return view('admin.users.edit', compact('user', 'userOrders', 'userPayments', 'userStats'));
    }

    /**
     * Update the specified user.
     */
    public function postUpdate(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $id],
            'role' => ['required', 'in:user,reseller,admin'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:2'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $this->validator($request->all(), $rules)->validate();

        DB::beginTransaction();

        try {
            $oldEmail = $user->email;

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            // Update profile
            $profileData = [
                'country' => $request->country,
                'timezone' => $request->timezone ?? 'UTC',
            ];

            // Update balance if provided
            if ($request->has('balance')) {
                $newBalance = (float) $request->balance;
                $currentBalance = $user->profile->balance ?? 0;

                if ($newBalance != $currentBalance) {
                    $profileData['balance'] = $newBalance;
                }
            }

            $user->profile->update($profileData);

            // Update custom rates
            if ($request->has('custom_rates')) {
                $user->customRates()->detach();
                foreach ($request->custom_rates as $serviceId => $rate) {
                    if ($rate !== null && $rate !== '') {
                        $user->customRates()->attach($serviceId, ['rate' => $rate]);
                    }
                }
            }

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Updated user: {$user->email}");

            DB::commit();

            return back()->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user suspension status.
     */
    public function postSuspend(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        // Prevent self-suspension
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'suspended';

        // Create activity log
        activity()
            ->causedBy(auth()->user())
            ->log("User {$user->email} has been {$status}");

        return back()->with('success', "User has been {$status}.");
    }

    /**
     * Delete a user.
     */
    public function postDelete(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check if user has orders
        if ($user->orders()->count() > 0) {
            return back()->with('error', 'Cannot delete user with existing orders. Consider suspending instead.');
        }

        $userEmail = $user->email;

        DB::beginTransaction();

        try {
            // Delete related records
            $user->profile->delete();
            $user->customRates()->detach();

            // Create activity log before deletion
            activity()
                ->causedBy(auth()->user())
                ->log("Deleted user: {$userEmail}");

            $user->delete();

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Export users to CSV.
     */
    public function getExport(Request $request)
    {
        $query = User::with('profile');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        $csvData[] = ['ID', 'Name', 'Email', 'Role', 'Balance', 'Total Spent', 'Orders', 'Status', 'Created At', 'Last Login'];

        foreach ($users as $user) {
            $csvData[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                number_format($user->profile->balance ?? 0, 2),
                number_format($user->profile->total_spent ?? 0, 2),
                $user->profile->total_orders ?? 0,
                $user->is_active ? 'Active' : 'Suspended',
                $user->created_at->format('Y-m-d H:i:s'),
                $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
            ];
        }

        $filename = 'users_export_' . date('Y-m-d_His') . '.csv';

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
     * Get a validator for an incoming validation request.
     */
    protected function validator(array $data, array $rules = [])
    {
        $defaultRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:user,reseller,admin'],
            'balance' => ['nullable', 'numeric', 'min:0'],
        ];

        return Validator::make($data, array_merge($defaultRules, $rules));
    }

    /**
     * Add or subtract balance from a user.
     */
    public function postAdjustBalance(Request $request, int $id)
    {
        $user = User::with('profile')->findOrFail($id);

        $request->validate([
            'amount' => ['required', 'numeric'],
            'type' => ['required', 'in:add,subtract'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (float) $request->amount;
        $type = $request->type;

        if ($type === 'subtract' && $amount > $user->profile->balance) {
            return back()->with('error', 'Cannot subtract more than current balance.');
        }

        DB::beginTransaction();

        try {
            if ($type === 'add') {
                $user->profile->balance += $amount;
            } else {
                $user->profile->balance -= $amount;
            }

            $user->profile->save();

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Balance adjustment for {$user->email}: " . ($type === 'add' ? '+' : '-') . "{$amount}");

            DB::commit();

            return back()->with('success', 'Balance adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to adjust balance.');
        }
    }
}
