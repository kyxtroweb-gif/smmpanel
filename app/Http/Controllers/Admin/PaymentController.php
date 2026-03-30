<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function getIndex(Request $request): View
    {
        $query = Payment::with(['user', 'paymentMethod']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('user_txn_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('method', $request->method);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $payments = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $stats = [
            'total_completed' => Payment::where('status', 'completed')->sum('net_amount'),
            'total_pending' => Payment::whereIn('status', ['pending', 'approved'])->sum('net_amount'),
            'pending_count' => Payment::where('status', 'pending')->count(),
            'this_month' => Payment::whereMonth('created_at', now()->month)
                ->where('status', 'completed')->sum('net_amount'),
        ];

        $methods = PaymentMethod::orderBy('name')->get();

        return view('admin.payments.index', compact('payments', 'stats', 'methods'));
    }

    public function getView(int $id): View
    {
        $payment = Payment::with(['user', 'paymentMethod'])->findOrFail($id);
        return view('admin.payments.view', compact('payment'));
    }

    /**
     * Approve a pending payment — auto-credits user balance.
     * For manual QR/TXN methods like PayTM, UPI, etc.
     */
    public function postApprove(Request $request, int $id): RedirectResponse
    {
        $payment = Payment::with('user')->findOrFail($id);

        if ($payment->status === 'completed') {
            return back()->with('error', 'This payment is already completed.');
        }

        $v = Validator::make($request->all(), [
            'admin_amount' => 'nullable|numeric|min:0',
            'admin_bonus' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);
        $v->validate();

        DB::beginTransaction();
        try {
            if ($request->filled('admin_amount')) {
                $payment->net_amount = $request->admin_amount;
            }
            if ($request->filled('admin_bonus')) {
                $payment->amount_bonus = $request->admin_bonus;
            }
            if ($request->filled('note')) {
                $payment->note = trim(($payment->note ?? '') . "\n[Admin: {$request->note}]");
            }

            $payment->creditUser();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'payment_approved',
                'description' => "Approved payment {$payment->transaction_id} — credited {$payment->net_amount} to {$payment->user->name}",
            ]);

            DB::commit();

            return redirect()->route('admin.payments.view', $id)
                ->with('success', "Payment approved. {$payment->user->name}'s balance credited with {$payment->net_amount}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending payment.
     */
    public function postReject(Request $request, int $id): RedirectResponse
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status === 'completed') {
            return back()->with('error', 'Cannot reject a completed payment.');
        }

        $request->validate([
            'reject_reason' => 'required|string|max:500',
        ]);

        $payment->status = 'failed';
        $payment->note = trim(($payment->note ?? '') . "\n[Rejected: {$request->reject_reason}]");
        $payment->save();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'payment_rejected',
            'description' => "Rejected payment {$payment->transaction_id}: {$request->reject_reason}",
        ]);

        return redirect()->route('admin.payments.view', $id)
            ->with('success', 'Payment rejected.');
    }

    /**
     * Refund a completed payment.
     */
    public function postRefund(Request $request, int $id): RedirectResponse
    {
        $payment = Payment::with('user')->findOrFail($id);

        if ($payment->status !== 'completed') {
            return back()->with('error', 'Can only refund completed payments.');
        }

        $request->validate([
            'refund_reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $user = $payment->user;

            $user->balance -= (float) $payment->net_amount;
            $user->save();

            Transaction::create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'type' => 'refund',
                'amount' => -(float) $payment->net_amount,
                'balance' => $user->balance,
                'reference' => 'refund:' . $payment->transaction_id,
                'description' => "Refund for {$payment->transaction_id}: " . ($request->refund_reason ?? 'No reason'),
            ]);

            $payment->status = 'refunded';
            $payment->note = trim(($payment->note ?? '') . "\n[Refunded: {$request->refund_reason}]");
            $payment->save();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'payment_refunded',
                'description' => "Refunded {$payment->net_amount} for payment {$payment->transaction_id} to {$user->name}",
            ]);

            DB::commit();

            return redirect()->route('admin.payments.view', $id)
                ->with('success', 'Payment refunded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getExport(Request $request)
    {
        $query = Payment::with(['user', 'paymentMethod']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $payments = $query->orderByDesc('created_at')->get();

        $csv = "ID,Transaction ID,User TXN ID,User,Method,Amount,Bonus,Net Amount,Status,Date\n";
        foreach ($payments as $p) {
            $csv .= implode(',', [
                $p->id,
                $p->transaction_id,
                $p->user_txn_id ?? '',
                $p->user->name ?? '',
                $p->method,
                $p->amount,
                $p->amount_bonus,
                $p->net_amount,
                $p->status,
                $p->created_at,
            ]) . "\n";
        }

        return response()->streamDownload(
            fn() => print($csv),
            'payments-' . date('Y-m-d') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
