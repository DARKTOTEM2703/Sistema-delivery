<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $restaurant->is_open_now = $restaurant->isOpen();
            $restaurant->average_delivery_time = $restaurant->getAverageDeliveryTime();
            $restaurant->today_orders = $restaurant->getTodayOrders();
            
            return $restaurant;
        });

        return response()->json($restaurants);
    }

    public function show($id)
    {
        $restaurant = Restaurant::with(['owner', 'products' => function($query) {
            $query->where('is_available', true);
        }])->findOrFail($id);

        $restaurant->is_open_now = $restaurant->isOpen();
        $restaurant->average_delivery_time = $restaurant->getAverageDeliveryTime();

        return response()->json($restaurant);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Restaurant::class);

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

        $validated['slug'] = Str::slug($validated['name']);
        $validated['owner_id'] = Auth::id();

        // Verificar que el slug sea único
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Restaurant::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $restaurant = Restaurant::create($validated);

        // Asignar rol de owner al usuario
        Auth::user()->assignRole('owner', $restaurant->id);

        return response()->json([
            'restaurant' => $restaurant,
            'message' => 'Restaurante creado exitosamente'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        
        $this->authorize('update', $restaurant);

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
        $restaurant = Restaurant::findOrFail($id);
        
        $this->authorize('delete', $restaurant);

        $restaurant->delete();

        return response()->json([
            'message' => 'Restaurante eliminado exitosamente'
        ]);
    }

    public function getCategories()
    {
        $categories = Restaurant::select('category')
            ->distinct()
            ->where('is_active', true)
            ->pluck('category');

        return response()->json($categories);
    }

    public function getEmployees($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        
        $this->authorize('view', $restaurant);

        $employees = $restaurant->employees()
            ->with(['roles' => function($query) use ($id) {
                $query->wherePivot('restaurant_id', $id);
            }])
            ->wherePivot('is_active', true)
            ->get();

        return response()->json($employees);
    }

    public function getDashboardStats($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        
        $this->authorize('view', $restaurant);

        $stats = [
            'today_orders' => $restaurant->getTodayOrders(),
            'today_revenue' => $restaurant->getTodayRevenue(),
            'pending_orders' => $restaurant->orders()->where('status', 'pending')->count(),
            'active_employees' => $restaurant->employees()->wherePivot('is_active', true)->count(),
            'total_products' => $restaurant->products()->where('is_available', true)->count(),
            'average_rating' => $restaurant->rating,
            'total_reviews' => $restaurant->total_reviews,
        ];

        return response()->json($stats);
    }
}