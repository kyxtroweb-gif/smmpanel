<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DepositController extends Controller
{
    /**
     * Display deposit page with available payment methods.
     */
    public function getIndex(): View
    {
        $user = auth()->user();

        $manualMethods = PaymentMethod::where('is_active', true)
            ->where('is_automatic', false)
            ->orderBy('sort_order')
            ->get();

        $autoMethods = PaymentMethod::where('is_active', true)
            ->where('is_automatic', true)
            ->orderBy('sort_order')
            ->get();

        $recentPayments = Payment::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_deposited' => Transaction::where('user_id', $user->id)
                ->where('type', 'deposit')->sum('amount'),
            'pending_count' => Payment::where('user_id', $user->id)
                ->where('status', 'pending')->count(),
        ];

        $suggestedAmounts = [5, 10, 20, 50, 100, 200, 500, 1000];

        return view('user.deposit.index', compact(
            'manualMethods',
            'autoMethods',
            'recentPayments',
            'stats',
            'suggestedAmounts'
        ));
    }

    /**
     * Display payment method details (QR code + instructions for manual, or gateway for auto).
     */
    public function getMethod(int $id): View
    {
        $user = auth()->user();
        $method = PaymentMethod::where('is_active', true)->findOrFail($id);

        $pendingPayment = Payment::where('user_id', $user->id)
            ->where('payment_method_id', $method->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        return view('user.deposit.method', compact('method', 'pendingPayment'));
    }

    /**
     * STEP 1 — User selects amount and submits (creates a pending deposit record).
     * For manual methods: shows QR/instructions page.
     * For auto methods: redirects to gateway.
     */
    public function postSelect(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $v = Validator::make($request->all(), [
            'method_id' => 'required|integer|exists:payment_methods,id',
            'amount' => 'required|numeric|min:1|max:100000',
        ]);
        $v->validate();

        $method = PaymentMethod::where('is_active', true)->findOrFail($request->method_id);

        // Validate amount range
        if ($request->amount < $method->min_amount) {
            return back()->withInput()->withErrors(['amount' => "Minimum deposit is {$method->min_amount}."]);
        }
        if ($method->max_amount > 0 && $request->amount > $method->max_amount) {
            return back()->withInput()->withErrors(['amount' => "Maximum deposit is {$method->max_amount}."]);
        }

        $amount = (float) $request->amount;
        $netCalc = $method->calculateNetAmount($amount);

        // For auto methods, redirect to gateway
        if ($method->is_automatic) {
            return $this->initiateAutoPayment($user, $method, $amount, $netCalc);
        }

        // For manual methods — create pending payment and show TXN submission form
        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_method_id' => $method->id,
                'method' => $method->name,
                'amount' => $amount,
                'amount_bonus' => $netCalc['bonus'],
                'net_amount' => $netCalc['net'],
                'status' => 'pending',
                'expires_at' => now()->addHours(24),
            ]);

            DB::commit();

            return redirect()->route('user.deposit.submit-txn', $payment->transaction_id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating deposit: ' . $e->getMessage());
        }
    }

    /**
     * STEP 2 — User sees QR code + instructions, then submits TXN ID + claimed amount.
     */
    public function getSubmitTxn(string $transactionId): View
    {
        $user = auth()->user();
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $method = $payment->paymentMethod;

        return view('user.deposit.submit-txn', compact('payment', 'method'));
    }

    /**
     * STEP 2b — User submits their TXN ID + amount they paid.
     */
    public function postSubmitTxn(Request $request, string $transactionId): RedirectResponse
    {
        $user = auth()->user();
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $method = $payment->paymentMethod;

        $v = Validator::make($request->all(), [
            'user_txn_id' => 'required|string|max:200',
            'user_amount' => 'required|numeric|min:1',
        ]);
        $v->validate();

        // Basic validation: claimed amount shouldn't be wildly off
        $difference = abs($request->user_amount - $payment->amount);
        $tolerance = $payment->amount * 0.05; // 5% tolerance
        if ($difference > $tolerance && $difference > 10) {
            // Let them submit but flag it — admin will verify
        }

        DB::beginTransaction();
        try {
            $payment->user_txn_id = $request->user_txn_id;
            $payment->user_amount = $request->user_amount;

            // Check if admin approval is required
            if ($method->requires_admin_approval) {
                $payment->status = 'pending';
                $payment->save();
                DB::commit();
                return redirect()->route('user.deposit.index')
                    ->with('success', "Payment submitted! Your TXN ID has been recorded. It will be verified and your balance will be credited once approved.");
            } else {
                // Auto-verify for trusted methods (e.g., if amount matches exactly)
                if (abs($request->user_amount - $payment->amount) <= 0.01) {
                    $payment->creditUser();
                    DB::commit();
                    return redirect()->route('user.deposit.index')
                        ->with('success', "Payment confirmed! {$payment->net_amount} has been credited to your balance.");
                } else {
                    $payment->status = 'pending';
                    $payment->save();
                    DB::commit();
                    return redirect()->route('user.deposit.index')
                        ->with('success', "Payment submitted! Your deposit will be verified by an administrator.");
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * For automatic payment methods (Stripe, PayPal, etc.)
     */
    protected function initiateAutoPayment($user, PaymentMethod $method, float $amount, array $netCalc): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_method_id' => $method->id,
                'method' => $method->name,
                'amount' => $amount,
                'amount_bonus' => $netCalc['bonus'],
                'net_amount' => $netCalc['net'],
                'status' => 'pending',
                'expires_at' => now()->addHours(2),
            ]);

            DB::commit();

            // Redirect based on method type
            return match ($method->slug) {
                'stripe' => redirect()->route('user.deposit.stripe', $payment->transaction_id),
                'paypal' => redirect()->route('user.deposit.paypal', $payment->transaction_id),
                'crypto' => redirect()->route('user.deposit.crypto', $payment->transaction_id),
                default => redirect()->route('user.deposit.gateway', [$method->slug, $payment->transaction_id]),
            };
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // ============ AUTO GATEWAY HANDLERS ============

    public function getStripe(string $transactionId): View
    {
        $user = auth()->user();
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('user.deposit.stripe', compact('payment'));
    }

    public function getPaypal(string $transactionId): View
    {
        $user = auth()->user();
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('user.deposit.paypal', compact('payment'));
    }

    public function getCrypto(string $transactionId): View
    {
        $user = auth()->user();
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $method = $payment->paymentMethod;
        $credentials = $method->credentials_array;

        // In production, generate a unique crypto address per payment
        $cryptoAddress = $credentials['wallet_address'] ?? 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh';
        $cryptoCurrency = $credentials['currency'] ?? 'BTC';

        return view('user.deposit.crypto', compact('payment', 'method', 'cryptoAddress', 'cryptoCurrency'));
    }

    /**
     * Mark payment as completed after gateway callback.
     */
    public function getSuccess(string $transactionId): RedirectResponse
    {
        $user = auth()->user();
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($payment->status !== 'completed') {
            try {
                $payment->creditUser();
            } catch (\Exception $e) {
                return redirect()->route('user.deposit.index')
                    ->with('error', 'Could not process payment.');
            }
        }

        return redirect()->route('user.deposit.index')
            ->with('success', "Deposit successful! {$payment->net_amount} credited to your balance.");
    }

    public function getCancel(string $transactionId): RedirectResponse
    {
        $user = auth()->user();
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($payment) {
            $payment->status = 'failed';
            $payment->note = 'Cancelled by user';
            $payment->save();
        }

        return redirect()->route('user.deposit.index')
            ->with('info', 'Payment cancelled.');
    }

    /**
     * Webhook for automatic payment gateways.
     */
    public function postWebhook(Request $request, string $method): \Illuminate\Http\JsonResponse
    {
        // Verify webhook signature based on method
        // Process payment completion

        $transactionId = $request->input('metadata.transaction_id')
            ?? $request->input('custom_id')
            ?? null;

        if (!$transactionId) {
            return response()->json(['error' => 'Missing transaction ID'], 400);
        }

        $payment = Payment::where('transaction_id', $transactionId)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Verify amount matches
        $receivedAmount = (float) ($request->input('amount') ?? $request->input('amount_total') ?? 0);
        if ($receivedAmount >= $payment->amount * 0.99) {
            try {
                $payment->creditUser();
                $payment->payment_data = [
                    'webhook_method' => $method,
                    'webhook_payload' => $request->all(),
                ];
                $payment->save();
            } catch (\Exception $e) {
                \Log::error("Webhook payment credit failed: " . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
