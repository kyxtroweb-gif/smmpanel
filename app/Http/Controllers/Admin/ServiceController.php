<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of services.
     */
    public function getIndex(Request $request)
    {
        $query = Service::with(['category:id,name', 'provider:id,name']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('service_id', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by provider
        if ($request->has('provider') && $request->provider) {
            $query->where('provider_id', $request->provider);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $services = $query->orderBy('category_id')
            ->orderBy('name')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $providers = Provider::where('is_active', true)->orderBy('name')->get();

        return view('admin.services.index', compact('services', 'categories', 'providers'));
    }

    /**
     * Show the create service form.
     */
    public function getCreate()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $providers = Provider::where('is_active', true)->orderBy('name')->get();

        return view('admin.services.create', compact('categories', 'providers'));
    }

    /**
     * Store a newly created service.
     */
    public function postStore(Request $request)
    {
        $this->validator($request->all())->validate();

        DB::beginTransaction();

        try {
            $service = Service::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'provider_id' => $request->provider_id,
                'service_id' => $request->service_id,
                'type' => $request->type ?? 'default',
                'price_per_item' => $request->price_per_item,
                'minimum_quantity' => $request->minimum_quantity ?? 1,
                'maximum_quantity' => $request->maximum_quantity ?? 10000,
                'average_time' => $request->average_time ?? 0,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'refill' => $request->boolean('refill'),
            ]);

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Created service: {$service->name}");

            DB::commit();

            return redirect()->route('admin.services.edit', $service->id)
                ->with('success', 'Service created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create service: ' . $e->getMessage());
        }
    }

    /**
     * Show the edit service form.
     */
    public function getEdit(int $id)
    {
        $service = Service::with(['category', 'provider'])->findOrFail($id);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $providers = Provider::where('is_active', true)->orderBy('name')->get();

        // Get related orders count
        $ordersCount = $service->orders()->count();

        return view('admin.services.edit', compact('service', 'categories', 'providers', 'ordersCount'));
    }

    /**
     * Update the specified service.
     */
    public function postUpdate(Request $request, int $id)
    {
        $service = Service::findOrFail($id);

        $this->validator($request->all(), $id)->validate();

        DB::beginTransaction();

        try {
            $service->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'provider_id' => $request->provider_id,
                'service_id' => $request->service_id,
                'type' => $request->type ?? 'default',
                'price_per_item' => $request->price_per_item,
                'minimum_quantity' => $request->minimum_quantity ?? 1,
                'maximum_quantity' => $request->maximum_quantity ?? 10000,
                'average_time' => $request->average_time ?? 0,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active'),
                'refill' => $request->boolean('refill'),
            ]);

            // Create activity log
            activity()
                ->causedBy(auth()->user())
                ->log("Updated service: {$service->name}");

            DB::commit();

            return back()->with('success', 'Service updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update service: ' . $e->getMessage());
        }
    }

    /**
     * Delete a service.
     */
    public function postDelete(Request $request, int $id)
    {
        $service = Service::findOrFail($id);

        // Check if service has orders
        if ($service->orders()->count() > 0) {
            // Soft disable instead of delete
            $service->update(['is_active' => false]);
            return back()->with('warning', 'Service has existing orders. It has been disabled instead of deleted.');
        }

        $serviceName = $service->name;

        $service->delete();

        // Create activity log
        activity()
            ->causedBy(auth()->user())
            ->log("Deleted service: {$serviceName}");

        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    /**
     * Bulk action on services.
     */
    public function postBulkAction(Request $request)
    {
        $request->validate([
            'service_ids' => ['required', 'array', 'min:1'],
            'action' => ['required', 'in:enable,disable,delete,update_rates'],
            'rate_multiplier' => ['nullable', 'numeric', 'min:0.1', 'max:10'],
        ]);

        $serviceIds = $request->service_ids;
        $action = $request->action;

        DB::beginTransaction();

        try {
            $query = Service::whereIn('id', $serviceIds);

            switch ($action) {
                case 'enable':
                    $query->update(['is_active' => true]);
                    $count = $query->count();
                    activity()
                        ->causedBy(auth()->user())
                        ->log("Bulk enabled {$count} services");
                    break;

                case 'disable':
                    $query->update(['is_active' => false]);
                    $count = $query->count();
                    activity()
                        ->causedBy(auth()->user())
                        ->log("Bulk disabled {$count} services");
                    break;

                case 'delete':
                    // Only delete services without orders
                    $servicesToDelete = $query->whereDoesntHave('orders')->get();
                    $count = $servicesToDelete->count();
                    foreach ($servicesToDelete as $service) {
                        $service->delete();
                    }
                    activity()
                        ->causedBy(auth()->user())
                        ->log("Bulk deleted {$count} services");
                    break;

                case 'update_rates':
                    if (!$request->has('rate_multiplier')) {
                        throw new \Exception('Rate multiplier is required for this action.');
                    }
                    $multiplier = (float) $request->rate_multiplier;
                    $services = $query->get();
                    foreach ($services as $service) {
                        $service->price_per_item = $service->price_per_item * $multiplier;
                        $service->save();
                    }
                    activity()
                        ->causedBy(auth()->user())
                        ->log("Bulk updated rates with multiplier {$multiplier} for {$services->count()} services");
                    break;
            }

            DB::commit();

            return back()->with('success', 'Bulk action completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    /**
     * Get a validator for an incoming validation request.
     */
    protected function validator(array $data, int $id = null)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'provider_id' => ['nullable', 'exists:providers,id'],
            'service_id' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:50'],
            'price_per_item' => ['required', 'numeric', 'min:0'],
            'minimum_quantity' => ['nullable', 'integer', 'min:1'],
            'maximum_quantity' => ['nullable', 'integer', 'min:1'],
            'average_time' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'refill' => ['nullable', 'boolean'],
        ];

        if ($id) {
            $rules['service_id'][] = 'unique:services,service_id,' . $id;
        }

        return Validator::make($data, $rules);
    }

    /**
     * Get services by category for API.
     */
    public function getByCategory(int $categoryId)
    {
        $services = Service::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($services);
    }

    /**
     * Toggle service active status.
     */
    public function postToggle(int $id)
    {
        $service = Service::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        $status = $service->is_active ? 'enabled' : 'disabled';

        activity()
            ->causedBy(auth()->user())
            ->log("Service {$service->name} has been {$status}");

        return back()->with('success', "Service has been {$status}.");
    }
}
