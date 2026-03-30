<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Models\Refill;
use App\Services\OrderDispatchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserApiController extends Controller
{
    protected OrderDispatchService $orderDispatch;

    public function __construct(OrderDispatchService $orderDispatch)
    {
        $this->orderDispatch = $orderDispatch;
    }

    /**
     * Authenticate API request and return user.
     */
    protected function authenticateApiRequest(Request $request): JsonResponse|User
    {
        $apiKey = $request->get('api_key') ?? $request->header('X-API-KEY');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key is required',
                'code' => 'MISSING_API_KEY',
            ], 401);
        }

        $user = User::where('api_key', $apiKey)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Invalid API key',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        if (!$user->api_enabled) {
            return response()->json([
                'error' => 'API access is disabled for this account',
                'code' => 'API_DISABLED',
            ], 403);
        }

        return $user;
    }

    /**
     * Get active services with rates for authenticated user.
     */
    public function getServices(Request $request): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        $query = Service::with('category:id,name', 'provider:id,name')
            ->where('status', 'active');

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by service ID
        if ($request->filled('service_id')) {
            $query->where('id', $request->service_id);
        }

        $services = $query->orderBy('category_id')
            ->orderBy('price', 'asc')
            ->get()
            ->map(function ($service) use ($user) {
                return [
                    'service' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category->name ?? 'Unknown',
                    'rate' => number_format($service->getPriceForUser($user), 4, '.', ''),
                    'min' => (int) $service->min_quantity,
                    'max' => (int) $service->max_quantity,
                    'description' => $service->description,
                    'average_time' => $service->average_time,
                    'dripfeed' => (bool) $service->dripfeed,
                    'refill' => (bool) $service->refill,
                ];
            });

        return response()->json([
            'services' => $services,
        ]);
    }

    /**
     * Get user balance.
     */
    public function getBalance(Request $request): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        return response()->json([
            'balance' => number_format($user->balance, 2, '.', ''),
            'currency' => 'USD',
        ]);
    }

    /**
     * Place order via API.
     */
    public function postOrder(Request $request): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        $validator = Validator::make($request->all(), [
            'service' => 'required|integer',
            'link' => 'required|url',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'code' => 'VALIDATION_ERROR',
            ], 400);
        }

        $service = Service::where('status', 'active')
            ->find($request->service);

        if (!$service) {
            return response()->json([
                'error' => 'Service not found',
                'code' => 'SERVICE_NOT_FOUND',
            ], 404);
        }

        // Validate quantity limits
        if ($request->quantity < $service->min_quantity) {
            return response()->json([
                'error' => "Minimum quantity is {$service->min_quantity}",
                'code' => 'QUANTITY_TOO_LOW',
            ], 400);
        }

        if ($request->quantity > $service->max_quantity) {
            return response()->json([
                'error' => "Maximum quantity is {$service->max_quantity}",
                'code' => 'QUANTITY_TOO_HIGH',
            ], 400);
        }

        // Calculate price
        $price = $service->getPriceForUser($user);
        $total = $price * $request->quantity;

        // Check balance
        if ($user->balance < $total) {
            return response()->json([
                'error' => 'Insufficient balance',
                'code' => 'INSUFFICIENT_BALANCE',
            ], 400);
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
                'link' => $request->link,
                'quantity' => $request->quantity,
                'price' => $price,
                'total' => $total,
                'status' => 'pending',
                'mode' => 'api',
            ]);

            // Create transaction
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'order',
                'amount' => -$total,
                'balance' => $user->balance,
                'reference' => 'api_order:' . $order->id,
                'description' => "API Order #{$order->order_number}",
            ]);

            DB::commit();

            // Dispatch to provider
            $this->orderDispatch->dispatch($order);

            return response()->json([
                'order' => $order->id,
                'status' => 'pending',
                'charge' => number_format($total, 2, '.', ''),
                'balance' => number_format($user->fresh()->balance, 2, '.', ''),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create order: ' . $e->getMessage(),
                'code' => 'ORDER_FAILED',
            ], 500);
        }
    }

    /**
     * Get order status.
     */
    public function getOrder(Request $request, string $orderId): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        // Try to find by numeric ID or order number
        $order = Order::where('user_id', $user->id)
            ->where(function ($query) use ($orderId) {
                $query->where('id', $orderId)
                    ->orWhere('order_number', $orderId);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'error' => 'Order not found',
                'code' => 'ORDER_NOT_FOUND',
            ], 404);
        }

        // Calculate progress
        $progress = 0;
        if ($order->status === 'completed') {
            $progress = 100;
        } elseif (in_array($order->status, ['in_progress', 'partial'])) {
            $delivered = $order->quantity - ($order->remains ?? 0);
            $progress = $order->quantity > 0 ? round(($delivered / $order->quantity) * 100, 2) : 0;
        }

        return response()->json([
            'order' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'service' => $order->service_id,
            'link' => $order->link,
            'quantity' => $order->quantity,
            'remains' => $order->remains ?? 0,
            'charge' => number_format($order->total, 2, '.', ''),
            'start_count' => $order->start_count ?? 0,
            'currency' => 'USD',
            'created_at' => $order->created_at->toIso8601String(),
            'updated_at' => $order->updated_at->toIso8601String(),
            'progress' => $progress,
        ]);
    }

    /**
     * Get multiple orders status.
     */
    public function getOrders(Request $request): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        $orderIds = $request->get('orders', []);

        if (empty($orderIds)) {
            return response()->json([
                'error' => 'No orders specified',
                'code' => 'MISSING_ORDERS',
            ], 400);
        }

        $orders = Order::where('user_id', $user->id)
            ->whereIn('id', $orderIds)
            ->orWhereIn('order_number', $orderIds)
            ->get()
            ->keyBy('id');

        $results = [];
        foreach ($orderIds as $id) {
            if (isset($orders[$id])) {
                $order = $orders[$id];
                $results[] = [
                    'order' => $order->id,
                    'status' => $order->status,
                    'remains' => $order->remains ?? 0,
                ];
            } else {
                $results[] = [
                    'order' => $id,
                    'error' => 'Order not found',
                    'code' => 'ORDER_NOT_FOUND',
                ];
            }
        }

        return response()->json(['orders' => $results]);
    }

    /**
     * Request refill via API.
     */
    public function postRefill(Request $request, string $orderId): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        $order = Order::where('user_id', $user->id)
            ->where(function ($query) use ($orderId) {
                $query->where('id', $orderId)
                    ->orWhere('order_number', $orderId);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'error' => 'Order not found',
                'code' => 'ORDER_NOT_FOUND',
            ], 404);
        }

        // Check if service supports refill
        if (!$order->service->refill) {
            return response()->json([
                'error' => 'Refill is not available for this service',
                'code' => 'REFILL_NOT_AVAILABLE',
            ], 400);
        }

        // Check if order is eligible
        if (!in_array($order->status, ['completed', 'partial'])) {
            return response()->json([
                'error' => 'Only completed or partially delivered orders can be refilled',
                'code' => 'ORDER_NOT_ELIGIBLE',
            ], 400);
        }

        // Check for existing refill request
        $existingRefill = Refill::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->first();

        if ($existingRefill) {
            return response()->json([
                'error' => 'A refill request is already pending',
                'code' => 'REFILL_PENDING',
            ], 400);
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
            ]);

            // Update order
            $order->refill_count = ($order->refill_count ?? 0) + 1;
            $order->save();

            DB::commit();

            // Process refill with provider
            $this->orderDispatch->processRefill($order);

            return response()->json([
                'refill' => $refill->id,
                'status' => 'pending',
                'message' => 'Refill request submitted',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to submit refill request',
                'code' => 'REFILL_FAILED',
            ], 500);
        }
    }

    /**
     * Get refill status.
     */
    public function getRefillStatus(Request $request, int $refillId): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        $refill = Refill::where('user_id', $user->id)
            ->where('id', $refillId)
            ->first();

        if (!$refill) {
            return response()->json([
                'error' => 'Refill not found',
                'code' => 'REFILL_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'refill' => $refill->id,
            'order' => $refill->order_id,
            'status' => $refill->status,
            'created_at' => $refill->created_at->toIso8601String(),
        ]);
    }

    /**
     * Calculate order price via API.
     */
    public function postCalculate(Request $request): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        $validator = Validator::make($request->all(), [
            'service' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
                'code' => 'VALIDATION_ERROR',
            ], 400);
        }

        $service = Service::where('status', 'active')
            ->find($request->service);

        if (!$service) {
            return response()->json([
                'error' => 'Service not found',
                'code' => 'SERVICE_NOT_FOUND',
            ], 404);
        }

        // Validate quantity
        if ($request->quantity < $service->min_quantity) {
            return response()->json([
                'error' => "Minimum quantity is {$service->min_quantity}",
                'code' => 'QUANTITY_TOO_LOW',
            ], 400);
        }

        if ($request->quantity > $service->max_quantity) {
            return response()->json([
                'error' => "Maximum quantity is {$service->max_quantity}",
                'code' => 'QUANTITY_TOO_HIGH',
            ], 400);
        }

        $price = $service->getPriceForUser($user);
        $total = $price * $request->quantity;

        return response()->json([
            'service' => $service->id,
            'quantity' => $request->quantity,
            'price_per_item' => number_format($price, 4, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'currency' => 'USD',
        ]);
    }

    /**
     * Get user profile via API.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'balance' => number_format($user->balance, 2, '.', ''),
            'total_spent' => number_format($user->total_spent ?? 0, 2, '.', ''),
            'total_orders' => $user->orders()->count(),
            'created_at' => $user->created_at->toIso8601String(),
        ]);
    }

    /**
     * Get order history via API.
     */
    public function getOrderHistory(Request $request): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $user = $authResult;

        $query = Order::with('service:id,name')
            ->where('user_id', $user->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $perPage = min($request->get('per_page', 25), 100);
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'orders' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Get available service categories.
     */
    public function getCategories(Request $request): JsonResponse
    {
        $authResult = $this->authenticateApiRequest($request);
        if ($authResult instanceof JsonResponse) {
            return $authResult;
        }

        $categories = \App\Models\Category::where('status', 'active')
            ->withCount(['services' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'services_count' => $category->services_count,
                ];
            });

        return response()->json([
            'categories' => $categories,
        ]);
    }
}
