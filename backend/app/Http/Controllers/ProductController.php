<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('restaurant')
            ->where('is_available', true)
            ->paginate(20);

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with('restaurant')->findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string',
            'image' => 'nullable|url',
            'prep_time' => 'nullable|integer|min:1',
            'servings' => 'nullable|string',
            'is_available' => 'boolean'
        ]);

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
        $user = Auth::user();

        // ✅ VERIFICACIÓN MÚLTIPLE
        $isAuthorized =
            // 1. Es super_admin/admin
            $user->role === 'admin' ||
            $user->hasRole('super_admin') ||

            // 2. Es owner directo del restaurante
            $user->id === $restaurant->owner_id ||

            // 3. Tiene el restaurante asignado
            ($user->role === 'owner' && $user->owned_restaurant_id === $restaurant->id) ||

            // 4. Tiene rol específico en ese restaurante
            $user->hasRole('owner', $restaurant->id);

        // Si no está autorizado
        if (!$isAuthorized) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $product = Product::create($validated);

        return response()->json([
            'product' => $product,
            'message' => 'Producto creado exitosamente'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $user = Auth::user();
        $restaurant = $product->restaurant;

        // ✅ Usar la misma verificación múltiple
        $isAuthorized =
            $user->role === 'admin' ||
            $user->hasRole('super_admin') ||
            $user->id === $restaurant->owner_id ||
            ($user->role === 'owner' && $user->owned_restaurant_id === $restaurant->id) ||
            $user->hasRole('owner', $restaurant->id);

        if (!$isAuthorized) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'category' => 'string',
            'image' => 'nullable|url',
            'prep_time' => 'nullable|integer|min:1',
            'servings' => 'nullable|string',
            'is_available' => 'boolean'
        ]);

        $product->update($validated);

        return response()->json([
            'product' => $product,
            'message' => 'Producto actualizado exitosamente'
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $user = Auth::user();
        $restaurant = $product->restaurant;

        // ✅ Usar la misma verificación múltiple
        $isAuthorized =
            $user->role === 'admin' ||
            $user->hasRole('super_admin') ||
            $user->id === $restaurant->owner_id ||
            ($user->role === 'owner' && $user->owned_restaurant_id === $restaurant->id) ||
            $user->hasRole('owner', $restaurant->id);

        if (!$isAuthorized) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ]);
    }

    // ✅ MÉTODO ESPECÍFICO PARA PRODUCTOS POR RESTAURANTE
    public function getByRestaurant($restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        $products = Product::where('restaurant_id', $restaurantId)
            ->where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($products);
    }
}
