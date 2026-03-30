<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function getIndex(Request $request): View
    {
        $query = PaymentMethod::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('is_automatic', $request->type === 'automatic');
        }

        $methods = $query->orderBy('sort_order')->paginate(20)->withQueryString();
        return view('admin.payment-methods.index', compact('methods'));
    }

    public function getCreate(): View
    {
        return view('admin.payment-methods.create');
    }

    public function postStore(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:manual,automatic',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_automatic' => 'nullable|boolean',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'fixed_charge' => 'nullable|numeric|min:0',
            'percent_charge' => 'nullable|numeric|min:0|max:100',
            'bonus_percent' => 'nullable|numeric|min:0|max:100',
            'bonus_threshold' => 'nullable|numeric|min:0',
            'requires_admin_approval' => 'nullable|boolean',
            'instructions' => 'nullable|string',
            'fields' => 'nullable|string',
            'qr_image' => 'nullable|image|max:5120',
        ]);
        $v->sometimes('credentials', 'required', fn($i) => $request->type === 'automatic');
        $v->validate();

        DB::beginTransaction();
        try {
            $slug = Str::slug($request->name);
            if (PaymentMethod::where('slug', $slug)->exists()) {
                $slug .= '-' . time();
            }

            $fields = [];
            if ($request->filled('fields')) {
                $decoded = json_decode($request->fields, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $fields = $decoded;
                }
            }

            $credentials = [];
            if ($request->filled('credentials')) {
                $decoded = json_decode($request->credentials, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $credentials = $decoded;
                }
            }

            $method = PaymentMethod::create([
                'name' => $request->name,
                'slug' => $slug,
                'type' => $request->type,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'is_automatic' => $request->boolean('is_automatic'),
                'min_amount' => $request->min_amount ?? 1,
                'max_amount' => $request->max_amount ?? 10000,
                'fixed_charge' => $request->fixed_charge ?? 0,
                'percent_charge' => $request->percent_charge ?? 0,
                'bonus_percent' => $request->bonus_percent ?? 0,
                'bonus_threshold' => $request->bonus_threshold ?? 0,
                'requires_admin_approval' => $request->boolean('requires_admin_approval'),
                'instructions' => $request->instructions,
                'fields' => json_encode($fields),
                'credentials' => json_encode($credentials),
                'sort_order' => (PaymentMethod::max('sort_order') ?? 0) + 1,
            ]);

            if ($request->hasFile('qr_image')) {
                $path = $request->file('qr_image')->store('payment-qr', 'public');
                $method->qr_image = $path;
                $method->save();
            }

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('payment-logos', 'public');
                $method->logo = $path;
                $method->save();
            }

            DB::commit();
            return redirect()->route('admin.payment-methods.edit', $method->id)
                ->with('success', 'Payment method created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getEdit(int $id): View
    {
        $method = PaymentMethod::findOrFail($id);
        return view('admin.payment-methods.edit', compact('method'));
    }

    public function postUpdate(Request $request, int $id): RedirectResponse
    {
        $method = PaymentMethod::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:manual,automatic',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_automatic' => 'nullable|boolean',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'fixed_charge' => 'nullable|numeric|min:0',
            'percent_charge' => 'nullable|numeric|min:0|max:100',
            'bonus_percent' => 'nullable|numeric|min:0|max:100',
            'bonus_threshold' => 'nullable|numeric|min:0',
            'requires_admin_approval' => 'nullable|boolean',
            'instructions' => 'nullable|string',
            'fields' => 'nullable|string',
            'qr_image' => 'nullable|image|max:5120',
            'logo' => 'nullable|image|max:5120',
            'remove_qr' => 'nullable|boolean',
            'remove_logo' => 'nullable|boolean',
        ]);
        $v->validate();

        DB::beginTransaction();
        try {
            $fields = [];
            if ($request->filled('fields')) {
                $decoded = json_decode($request->fields, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $fields = $decoded;
                }
            }

            $credentials = [];
            if ($request->filled('credentials')) {
                $decoded = json_decode($request->credentials, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $credentials = $decoded;
                }
            }

            $method->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'type' => $request->type,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'is_automatic' => $request->boolean('is_automatic'),
                'min_amount' => $request->min_amount ?? 1,
                'max_amount' => $request->max_amount ?? 10000,
                'fixed_charge' => $request->fixed_charge ?? 0,
                'percent_charge' => $request->percent_charge ?? 0,
                'bonus_percent' => $request->bonus_percent ?? 0,
                'bonus_threshold' => $request->bonus_threshold ?? 0,
                'requires_admin_approval' => $request->boolean('requires_admin_approval'),
                'instructions' => $request->instructions,
                'fields' => json_encode($fields),
                'credentials' => json_encode($credentials),
            ]);

            if ($request->boolean('remove_qr')) {
                $method->qr_image = null;
            }
            if ($request->hasFile('qr_image')) {
                $method->qr_image = $request->file('qr_image')->store('payment-qr', 'public');
            }

            if ($request->boolean('remove_logo')) {
                $method->logo = null;
            }
            if ($request->hasFile('logo')) {
                $method->logo = $request->file('logo')->store('payment-logos', 'public');
            }

            $method->save();
            DB::commit();

            return back()->with('success', 'Payment method updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function postDelete(int $id): RedirectResponse
    {
        $method = PaymentMethod::findOrFail($id);

        if ($method->payments()->count() > 0) {
            $method->is_active = false;
            $method->save();
            return back()->with('warning', 'Payment method has transactions. Disabled instead of deleted.');
        }

        $method->delete();
        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method deleted.');
    }

    public function postToggle(int $id): RedirectResponse
    {
        $method = PaymentMethod::findOrFail($id);
        $method->is_active = !$method->is_active;
        $method->save();
        return back()->with('success', 'Status updated.');
    }
}
