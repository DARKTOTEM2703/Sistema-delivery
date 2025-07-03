<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::with(['owner'])
            ->where('is_active', true);

        // Filtros
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('min_rating') && $request->min_rating) {
            $query->where('rating', '>=', $request->min_rating);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'rating');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'rating':
                $query->orderBy('rating', $sortOrder);
                break;
            case 'delivery_time':
                $query->orderBy('delivery_time_min', 'asc');
                break;
            case 'delivery_fee':
                $query->orderBy('delivery_fee', 'asc');
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            default:
                $query->orderBy('rating', 'desc');
        }

        $restaurants = $query->paginate(12);

        // Agregar información adicional
        $restaurants->getCollection()->transform(function ($restaurant) {
            $restaurant->is_open_now = $this->isRestaurantOpen($restaurant);
            $restaurant->average_delivery_time = $this->getAverageDeliveryTime($restaurant);
            $restaurant->today_orders = $this->getTodayOrders($restaurant);

            return $restaurant;
        });

        return response()->json($restaurants);
    }

    public function show($id)
    {
        $restaurant = Restaurant::with(['owner', 'products' => function ($query) {
            $query->where('is_available', true);
        }])->find($id);

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurante no encontrado'], 404);
        }

        $restaurant->is_open_now = $this->isRestaurantOpen($restaurant);
        $restaurant->average_delivery_time = $this->getAverageDeliveryTime($restaurant);

        return response()->json($restaurant);
    }

    public function store(Request $request)
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'category' => 'required|string',
            'delivery_fee' => 'numeric|min:0',
            'delivery_time_min' => 'integer|min:1',
            'delivery_time_max' => 'integer|min:1',
            'minimum_order' => 'numeric|min:0',
            'business_hours' => 'required|array',
            'delivery_zones' => 'required|array',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180',
        ]);

        try {
            DB::beginTransaction();

            $validated['slug'] = Str::slug($validated['name']);
            $validated['owner_id'] = Auth::id();
            $validated['is_active'] = true;
            $validated['rating'] = 0;
            $validated['total_reviews'] = 0;

            // Verificar que el slug sea único
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Restaurant::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            $restaurant = Restaurant::create($validated);

            // ✅ AGREGAR ESTA LÍNEA DESPUÉS DE CREAR RESTAURANTE
            Auth::user()->update([
                'role' => 'owner',
                'owned_restaurant_id' => $restaurant->id
            ]);

            // Asignar rol de owner al usuario (esto ya existe)
            try {
                Auth::user()->assignRole('owner', $restaurant->id);
            } catch (\Exception $e) {
                \Log::warning('No se pudo asignar rol de owner: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'restaurant' => $restaurant,
                'message' => 'Restaurante creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'error' => 'Error al crear restaurante: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurante no encontrado'], 404);
        }

        // Verificar permisos básicos
        if (!Auth::check() || (Auth::id() !== $restaurant->owner_id && !Auth::user()->isSuperAdmin())) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'address' => 'string',
            'phone' => 'string',
            'email' => 'email',
            'category' => 'string',
            'delivery_fee' => 'numeric|min:0',
            'delivery_time_min' => 'integer|min:1',
            'delivery_time_max' => 'integer|min:1',
            'minimum_order' => 'numeric|min:0',
            'business_hours' => 'array',
            'delivery_zones' => 'array',
            'latitude' => 'numeric|between:-90,90',
            'longitude' => 'numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        // Actualizar slug si el nombre cambió
        if (isset($validated['name']) && $validated['name'] !== $restaurant->name) {
            $validated['slug'] = Str::slug($validated['name']);

            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Restaurant::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $restaurant->update($validated);

        return response()->json([
            'restaurant' => $restaurant,
            'message' => 'Restaurante actualizado exitosamente'
        ]);
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurante no encontrado'], 404);
        }

        // Verificar permisos básicos
        if (!Auth::check() || (Auth::id() !== $restaurant->owner_id && !Auth::user()->isSuperAdmin())) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Soft delete - solo marcar como inactivo
        $restaurant->update(['is_active' => false]);

        return response()->json([
            'message' => 'Restaurante desactivado exitosamente'
        ]);
    }

    public function getCategories()
    {
        $categories = [
            'italiana',
            'mexicana',
            'china',
            'japonesa',
            'americana',
            'saludable',
            'vegetariana',
            'comida_rapida',
            'postres'
        ];

        return response()->json($categories);
    }

    public function getEmployees($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['error' => 'Restaurante no encontrado'], 404);
        }

        // Verificar permisos básicos
        if (!Auth::check() || (Auth::id() !== $restaurant->owner_id && !Auth::user()->isSuperAdmin())) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            // Obtener empleados a través de la tabla user_roles
            $employees = User::whereHas('roles', function ($query) use ($id) {
                $query->wherePivot('restaurant_id', $id)
                    ->wherePivot('is_active', true);
            })->with(['roles' => function ($query) use ($id) {
                $query->wherePivot('restaurant_id', $id);
            }])->get();

            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener empleados'], 500);
        }
    }

    public function getDashboardStats($restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        // Verificar que el usuario sea el dueño
        if (Auth::id() !== $restaurant->owner_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $today = now()->startOfDay();

        $stats = [
            'todayOrders' => $restaurant->orders()->whereDate('created_at', $today)->count(),
            'todayRevenue' => $restaurant->orders()->whereDate('created_at', $today)->sum('total'),
            'totalProducts' => $restaurant->products()->count(),
            'averageRating' => $restaurant->rating,
            'totalReviews' => $restaurant->total_reviews,
            'monthlyOrders' => $restaurant->orders()->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count(),
            'monthlyRevenue' => $restaurant->orders()->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->sum('total')
        ];

        return response()->json($stats);
    }

    // ========== MÉTODOS PRIVADOS (SIN DUPLICADOS) ==========

    private function getOverviewStats($restaurantId, $dateRange)
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange);

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Comparar con período anterior
        $previousRange = $this->getPreviousDateRange($dateRange);
        $previousOrders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $previousRange)
            ->count();

        $previousRevenue = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $previousRange)
            ->sum('total');

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'avg_order_value' => $avgOrderValue,
            'order_growth' => $this->calculateGrowth($totalOrders, $previousOrders),
            'revenue_growth' => $this->calculateGrowth($totalRevenue, $previousRevenue)
        ];
    }

    private function getOrderStats($restaurantId, $dateRange)
    {
        $statusCounts = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $avgPreparationTime = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->whereNotNull('prepared_at')
            ->whereNotNull('confirmed_at')
            ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, confirmed_at, prepared_at)'));

        $avgDeliveryTime = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->whereNotNull('delivered_at')
            ->whereNotNull('picked_up_at')
            ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, picked_up_at, delivered_at)'));

        return [
            'status_breakdown' => $statusCounts,
            'avg_preparation_time' => round($avgPreparationTime ?? 0, 1),
            'avg_delivery_time' => round($avgDeliveryTime ?? 0, 1),
            'completion_rate' => $this->getCompletionRate($restaurantId, $dateRange),
            'busiest_hours' => $this->getBusiestHours($restaurantId, $dateRange)
        ];
    }

    private function getRevenueStats($restaurantId, $dateRange)
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'completed');

        $grossRevenue = $orders->sum('total');
        $deliveryFees = $orders->sum('delivery_fee');
        $taxes = $orders->sum('tax_amount');
        $tips = $orders->sum('tip_amount');

        return [
            'gross_revenue' => $grossRevenue,
            'delivery_fees' => $deliveryFees,
            'taxes' => $taxes,
            'tips' => $tips,
            'net_revenue' => $grossRevenue - $taxes
        ];
    }

    private function getProductStats($restaurantId, $dateRange)
    {
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('products.restaurant_id', $restaurantId)
            ->whereBetween('orders.created_at', $dateRange)
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::where('restaurant_id', $restaurantId)
            ->where('is_available', false)
            ->count();

        return [
            'top_products' => $topProducts,
            'low_stock_count' => $lowStockProducts
        ];
    }

    private function getReviewStats($restaurantId, $dateRange)
    {
        $reviews = Review::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange);

        $totalReviews = $reviews->count();
        $avgRating = $reviews->avg('rating');
        $avgFoodRating = $reviews->avg('food_rating');
        $avgServiceRating = $reviews->avg('service_rating');
        $avgDeliveryRating = $reviews->avg('delivery_rating');

        $ratingDistribution = Review::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        $pendingResponses = Review::where('restaurant_id', $restaurantId)
            ->whereNull('response')
            ->count();

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($avgRating, 1),
            'average_food_rating' => round($avgFoodRating, 1),
            'average_service_rating' => round($avgServiceRating, 1),
            'average_delivery_rating' => round($avgDeliveryRating, 1),
            'rating_distribution' => $ratingDistribution,
            'pending_responses' => $pendingResponses
        ];
    }

    private function getCustomerStats($restaurantId, $dateRange)
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange);

        $uniqueCustomers = $orders->distinct('user_id')->count('user_id');
        $returningCustomers = $orders->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('count(*) > 1')
            ->count();

        $topCustomers = Order::where('restaurant_id', $restaurantId)
            ->with('user:id,name')
            ->select('user_id', DB::raw('count(*) as order_count'), DB::raw('sum(total) as total_spent'))
            ->groupBy('user_id')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        return [
            'unique_customers' => $uniqueCustomers,
            'returning_customers' => $returningCustomers,
            'retention_rate' => $uniqueCustomers > 0 ? round(($returningCustomers / $uniqueCustomers) * 100, 1) : 0,
            'top_customers' => $topCustomers
        ];
    }

    private function getChartData($restaurantId, $dateRange, $period)
    {
        $format = $this->getDateFormat($period);

        $orderChart = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->select(DB::raw("DATE_FORMAT(created_at, '$format') as period"), DB::raw('count(*) as orders'))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $statusChart = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return [
            'orders_over_time' => $orderChart,
            'orders_by_status' => $statusChart
        ];
    }

    // ========== HELPER METHODS ==========

    private function getDateRange($period)
    {
        switch ($period) {
            case 'today':
                return [now()->startOfDay(), now()->endOfDay()];
            case 'week':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'month':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'year':
                return [now()->startOfYear(), now()->endOfYear()];
            default:
                return [now()->startOfDay(), now()->endOfDay()];
        }
    }

    private function getPreviousDateRange($currentRange)
    {
        $start = Carbon::parse($currentRange[0]);
        $end = Carbon::parse($currentRange[1]);
        $diff = $start->diffInDays($end);

        return [
            $start->copy()->subDays($diff + 1),
            $start->copy()->subDay()
        ];
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function getDateFormat($period)
    {
        switch ($period) {
            case 'today':
                return '%H:00';
            case 'week':
                return '%a';
            case 'month':
                return '%d';
            case 'year':
                return '%m';
            default:
                return '%d';
        }
    }

    private function getCompletionRate($restaurantId, $dateRange)
    {
        $totalOrders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->count();

        $completedOrders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'completed')
            ->count();

        return $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0;
    }

    private function getBusiestHours($restaurantId, $dateRange)
    {
        return Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', $dateRange)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('hour')
            ->orderBy('order_count', 'desc')
            ->limit(5)
            ->get();
    }

    private function isRestaurantOpen($restaurant)
    {
        if (!$restaurant->business_hours) {
            return true; // Abierto por defecto si no hay horarios
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l'));
        $currentTime = $now->format('H:i');

        $hours = $restaurant->business_hours[$dayOfWeek] ?? null;

        if (!$hours || !($hours['is_open'] ?? false)) {
            return false;
        }

        return $currentTime >= ($hours['open'] ?? '00:00') &&
            $currentTime <= ($hours['close'] ?? '23:59');
    }

    private function getAverageDeliveryTime($restaurant)
    {
        $min = $restaurant->delivery_time_min ?? 30;
        $max = $restaurant->delivery_time_max ?? 45;
        return ($min + $max) / 2;
    }

    private function getTodayOrders($restaurant)
    {
        return $restaurant->orders()->whereDate('created_at', today())->count();
    }
}
