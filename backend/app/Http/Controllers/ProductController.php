<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price, // Convertir a número
                    'image' => $product->image,
                    'category' => $product->category,
                    'rating' => (float) $product->rating, // Convertir a número
                    'time' => $product->time,
                    'servings' => $product->servings,
                    'restaurant_id' => $product->restaurant_id,
                    'is_available' => $product->is_available,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener productos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            $products = Product::where('name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->get();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en la búsqueda'], 500);
        }
    }

    public function getByRestaurant($restaurantId)
    {
        try {
            $products = Product::where('restaurant_id', $restaurantId)
                              ->where('is_available', true)
                              ->get()
                              ->map(function ($product) {
                                  return [
                                      'id' => $product->id,
                                      'name' => $product->name,
                                      'description' => $product->description,
                                      'price' => (float) $product->price,
                                      'image' => $product->image,
                                      'category' => $product->category,
                                      'rating' => (float) $product->rating,
                                      'time' => $product->time,
                                      'servings' => $product->servings,
                                      'restaurant_id' => $product->restaurant_id,
                                      'is_available' => $product->is_available,
                                  ];
                              });

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener productos del restaurante',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|string',
                'category' => 'required|string',
                'rating' => 'nullable|numeric|between:1,5',
                'time' => 'nullable|string',
                'servings' => 'nullable|string',
                'restaurant_id' => 'required|exists:restaurants,id',
                'is_available' => 'boolean',
                'preparation_time' => 'nullable|integer|min:1',
                'allergens' => 'nullable|string'
            ]);

            $product = Product::create($validated);

            return response()->json([
                'product' => $product,
                'message' => 'Producto creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear producto',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'string|max:255',
                'description' => 'string',
                'price' => 'numeric|min:0',
                'image' => 'nullable|string',
                'category' => 'string',
                'rating' => 'nullable|numeric|between:1,5',
                'time' => 'nullable|string',
                'servings' => 'nullable|string',
                'is_available' => 'boolean',
                'preparation_time' => 'nullable|integer|min:1',
                'allergens' => 'nullable|string'
            ]);

            $product->update($validated);

            return response()->json([
                'product' => $product,
                'message' => 'Producto actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar producto',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'message' => 'Producto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar producto',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}