<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Restaurantes públicos - ✅ ORDEN IMPORTANTE
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/categories', [RestaurantController::class, 'getCategories']); // ✅ ANTES DE {id}
Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
Route::get('/restaurants/{id}/products', [ProductController::class, 'getByRestaurant']);
Route::get('/restaurants/{id}/reviews', [ReviewController::class, 'getRestaurantReviews']);

// Productos públicos
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Búsqueda
Route::get('/search', [RestaurantController::class, 'search']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::patch('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Crear restaurante
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::get('/restaurants/{id}/dashboard-stats', [RestaurantController::class, 'getDashboardStats']);

    // ✅ AGREGAR RUTAS PARA GESTIÓN DE PEDIDOS
    Route::get('/restaurants/{id}/orders', [OrderController::class, 'getRestaurantOrders']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Orders generales
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // Products (solo propietarios)
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/reviews/{id}/helpful', [ReviewController::class, 'markHelpful']);

    // Favorites
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'getUserFavorites']);
    Route::get('/favorites/check/{restaurantId}', [FavoriteController::class, 'checkFavorite']);
    Route::get('/favorites/stats', [FavoriteController::class, 'getFavoriteStats']);

    // Restaurant owner routes
    Route::middleware('role:owner')->group(function () {
        Route::patch('/restaurants/{id}/zones', [RestaurantController::class, 'updateDeliveryZones']);
    });
});

// Ruta de fallback para errores 404
Route::fallback(function () {
    return response()->json([
        'message' => 'Ruta no encontrada. Verifica la URL y el método HTTP.'
    ], 404);
});
