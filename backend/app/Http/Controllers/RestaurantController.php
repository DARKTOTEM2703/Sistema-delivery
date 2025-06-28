<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            $query->where(function($q) use ($request) {
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
        $restaurant = Restaurant::with(['owner', 'products' => function($query) {
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

            // Asignar rol de owner al usuario (usando try-catch para evitar errores)
            try {
                Auth::user()->assignRole('owner', $restaurant->id);
            } catch (\Exception $e) {
                // Si falla el rol, continuar sin error crítico
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
        try {
            $categories = Restaurant::select('category')
                ->distinct()
                ->where('is_active', true)
                ->whereNotNull('category')
                ->pluck('category')
                ->filter()
                ->values();

            return response()->json($categories);
        } catch (\Exception $e) {
            // Fallback con categorías predeterminadas
            $defaultCategories = [
                'italiana', 'americana', 'japonesa', 'mexicana', 
                'china', 'saludable', 'rapida', 'postres'
            ];
            
            return response()->json($defaultCategories);
        }
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
            $employees = User::whereHas('roles', function($query) use ($id) {
                $query->wherePivot('restaurant_id', $id)
                      ->wherePivot('is_active', true);
            })->with(['roles' => function($query) use ($id) {
                $query->wherePivot('restaurant_id', $id);
            }])->get();

            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener empleados'], 500);
        }
    }

    public function getDashboardStats($id)
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
            $stats = [
                'today_orders' => $this->getTodayOrders($restaurant),
                'today_revenue' => $this->getTodayRevenue($restaurant),
                'pending_orders' => $restaurant->orders()->where('status', 'pending')->count(),
                'active_employees' => $this->getActiveEmployees($restaurant),
                'total_products' => $restaurant->products()->where('is_available', true)->count(),
                'average_rating' => $restaurant->rating ?? 0,
                'total_reviews' => $restaurant->total_reviews ?? 0,
                'weekly_orders' => $this->getWeeklyOrders($restaurant),
                'monthly_revenue' => $this->getMonthlyRevenue($restaurant),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener estadísticas'], 500);
        }
    }

    // Métodos auxiliares para evitar errores del modelo
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

    private function getTodayRevenue($restaurant)
    {
        return $restaurant->orders()
                   ->whereDate('created_at', today())
                   ->where('status', 'completed')
                   ->sum('total') ?? 0;
    }

    private function getWeeklyOrders($restaurant)
    {
        return $restaurant->orders()
                   ->whereBetween('created_at', [
                       now()->startOfWeek(),
                       now()->endOfWeek()
                   ])
                   ->count();
    }

    private function getMonthlyRevenue($restaurant)
    {
        return $restaurant->orders()
                   ->whereMonth('created_at', now()->month)
                   ->whereYear('created_at', now()->year)
                   ->where('status', 'completed')
                   ->sum('total') ?? 0;
    }

    private function getActiveEmployees($restaurant)
    {
        return User::whereHas('roles', function($query) use ($restaurant) {
            $query->wherePivot('restaurant_id', $restaurant->id)
                  ->wherePivot('is_active', true);
        })->count();
    }
}