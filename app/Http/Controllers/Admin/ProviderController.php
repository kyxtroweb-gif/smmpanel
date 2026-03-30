<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function getIndex(Request $request): View
    {
        $query = Provider::withCount('services');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        $providers = $query->orderBy('name')->paginate(25)->withQueryString();
        return view('admin.providers.index', compact('providers'));
    }

    public function getCreate(): View
    {
        return view('admin.providers.create');
    }

    public function postStore(Request $request): RedirectResponse
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'api_url' => 'required|url',
            'api_key' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);
        $v->validate();

        DB::beginTransaction();
        try {
            $provider = Provider::create([
                'name' => $request->name,
                'api_url' => $request->api_url,
                'api_key' => $request->api_key,
                'is_active' => $request->boolean('is_active'),
            ]);

            // Test connection
            $testResult = $this->testConnection($provider);
            if (!$testResult['success']) {
                DB::rollBack();
                return back()->withInput()->with('error', 'API test failed: ' . ($testResult['message'] ?? 'Unknown error'));
            }
            if (isset($testResult['balance'])) {
                $provider->balance = $testResult['balance'];
                $provider->save();
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'provider_created',
                'description' => "Created provider: {$provider->name}",
            ]);

            DB::commit();
            return redirect()->route('admin.providers')->with('success', 'Provider created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Sync all services from provider and import/create local services.
     * This is the KEY feature — matches Perfect Panel's "Import from Provider".
     */
    public function postSyncServices(Request $request, int $id): RedirectResponse
    {
        $provider = Provider::findOrFail($id);

        $v = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'markup_percent' => 'nullable|numeric|min:0|max:1000',
            'update_existing' => 'nullable|boolean',
        ]);
        $v->validate();

        $markup = (float) ($request->markup_percent ?? 0);
        $categoryId = $request->category_id;
        $updateExisting = $request->boolean('update_existing');
        $importCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        try {
            $rawServices = $provider->fetchServices();

            if (empty($rawServices)) {
                return back()->with('error', 'No services returned from provider. Check API URL and key.');
            }

            DB::beginTransaction();
            foreach ($rawServices as $raw) {
                try {
                    // Parse provider service data
                    $providerServiceId = (string) ($raw['service'] ?? $raw['id'] ?? null);
                    if (!$providerServiceId) {
                        $skipCount++;
                        continue;
                    }

                    $name = $raw['name'] ?? $raw['title'] ?? "Service {$providerServiceId}";
                    $providerRate = (float) ($raw['rate'] ?? $raw['price'] ?? 0);
                    $minOrder = (int) ($raw['min'] ?? $raw['min_order'] ?? 1);
                    $maxOrder = (int) ($raw['max'] ?? $raw['max_order'] ?? 10000);
                    $averageTime = $raw['average_time'] ?? $raw['avg_time'] ?? $raw['delivery_time'] ?? 'N/A';
                    $serviceDescription = $raw['description'] ?? '';

                    // Calculate our price with markup
                    $ourRate = $providerRate > 0 ? $providerRate * (1 + $markup / 100) : 0;
                    $ourRate = round($ourRate, 4);
                    $ourCost = $providerRate;

                    // Find or determine category
                    $catId = $categoryId;
                    if (!$catId && !empty($raw['category'])) {
                        $catName = $raw['category'];
                        $catSlug = \Illuminate\Support\Str::slug($catName);
                        $category = Category::where('slug', $catSlug)->first();
                        if (!$category) {
                            $category = Category::create([
                                'name' => $catName,
                                'slug' => $catSlug,
                                'icon' => 'fa fa-globe',
                                'sort_order' => Category::max('sort_order') + 1,
                                'is_active' => true,
                            ]);
                        }
                        $catId = $category->id;
                    }
                    if (!$catId) {
                        $skipCount++;
                        continue;
                    }

                    // Check if service already exists from this provider
                    $existing = Service::where('provider_id', $provider->id)
                        ->where('provider_service_id', $providerServiceId)
                        ->first();

                    if ($existing) {
                        if ($updateExisting) {
                            $existing->update([
                                'name' => $name,
                                'category_id' => $catId,
                                'cost_per_1k' => $ourCost,
                                'price_per_1k' => $ourRate,
                                'min_order' => $minOrder,
                                'max_order' => $maxOrder,
                                'average_time' => $averageTime,
                                'description' => $serviceDescription,
                            ]);
                        }
                        $skipCount++;
                        continue;
                    }

                    // Create new service
                    Service::create([
                        'category_id' => $catId,
                        'provider_id' => $provider->id,
                        'name' => $name,
                        'description' => $serviceDescription,
                        'provider_service_id' => $providerServiceId,
                        'cost_per_1k' => $ourCost,
                        'price_per_1k' => $ourRate,
                        'min_order' => $minOrder,
                        'max_order' => $maxOrder,
                        'average_time' => $averageTime,
                        'dripfeed' => $minOrder >= 1000,
                        'refill' => ($raw['refill'] ?? $raw['refill_available'] ?? false),
                        'cancel' => ($raw['cancel'] ?? false),
                        'is_active' => true,
                    ]);

                    $importCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning("Failed to import service from {$provider->name}: " . $e->getMessage());
                }
            }

            $provider->last_sync_at = now();
            $provider->save();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'provider_services_synced',
                'description' => "Synced services from {$provider->name}: {$importCount} imported, {$skipCount} skipped, {$errorCount} errors",
            ]);

            DB::commit();

            return back()->with('success', "Sync complete. Imported: {$importCount}, Skipped: {$skipCount}, Errors: {$errorCount}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Quick sync — just fetch services count without importing.
     */
    public function postSync(Request $request, int $id): RedirectResponse
    {
        $provider = Provider::findOrFail($id);

        $rawServices = $provider->fetchServices();

        if (empty($rawServices)) {
            return back()->with('error', 'No services returned. Check API URL and key.');
        }

        $provider->last_sync_at = now();
        $provider->save();

        return back()->with('success', "Provider returned " . count($rawServices) . " services. Use 'Import Services' to add them to your panel.");
    }

    /**
     * Check and update provider balance.
     */
    public function postCheckBalance(Request $request, int $id): RedirectResponse
    {
        $provider = Provider::findOrFail($id);

        $success = $provider->syncBalance();

        if ($success) {
            return back()->with('success', "Balance updated: {$provider->balance}");
        }
        return back()->with('error', 'Failed to fetch balance. Check API credentials.');
    }

    public function postDelete(int $id): RedirectResponse
    {
        $provider = Provider::findOrFail($id);
        $name = $provider->name;

        // Unlink services rather than cascade delete
        Service::where('provider_id', $provider->id)->update(['provider_id' => null]);
        $provider->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'provider_deleted',
            'description' => "Deleted provider: {$name}",
        ]);

        return redirect()->route('admin.providers')->with('success', 'Provider deleted.');
    }

    /**
     * Test API connection.
     */
    protected function testConnection(Provider $provider): array
    {
        try {
            $response = Http::timeout(15)->post($provider->api_url, [
                'key' => $provider->api_key,
                'action' => 'balance',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'balance' => $data['balance'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'message' => 'API returned error: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
