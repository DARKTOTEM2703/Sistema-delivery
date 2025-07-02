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
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
Route::get('/restaurants/{id}/products', [ProductController::class, 'getByRestaurant']);
Route::get('/restaurants/{id}/reviews', [ReviewController::class, 'getRestaurantReviews']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/search', [RestaurantController::class, 'search']);
Route::get('/restaurants/categories', [RestaurantController::class, 'getCategories']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::patch('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/cancel', [OrderController::class, 'cancel']);

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
        Route::get('/dashboard/stats', [RestaurantController::class, 'getDashboardStats']);
        Route::get('/dashboard/orders', [OrderController::class, 'getRestaurantOrders']);
        Route::get('/dashboard/reviews', [ReviewController::class, 'getRestaurantReviews']);
        Route::post('/reviews/{id}/respond', [ReviewController::class, 'respond']);

        // Products management
        Route::post('/products', [ProductController::class, 'store']);
        Route::patch('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::patch('/products/{id}/toggle-availability', [ProductController::class, 'toggleAvailability']);

        // Restaurant management
        Route::patch('/restaurants/{id}', [RestaurantController::class, 'update']);
        Route::patch('/restaurants/{id}/hours', [RestaurantController::class, 'updateHours']);
        Route::patch('/restaurants/{id}/zones', [RestaurantController::class, 'updateDeliveryZones']);
    });

    // Delivery person routes
    Route::middleware('role:delivery')->group(function () {
        Route::get('/delivery/orders', [OrderController::class, 'getDeliveryOrders']);
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::post('/orders/{id}/location', [OrderController::class, 'updateLocation']);
    });
});

// Ruta de fallback para errores 404
Route::fallback(function () {
    return response()->json([
        'message' => 'Ruta no encontrada. Verifica la URL y el método HTTP.'
    ], 404);
});

Route::prefix('restaurants')->group(function () {
    Route::get('/', [RestaurantController::class, 'index']);
    Route::get('/categories', [RestaurantController::class, 'getCategories']); // ✅ AGREGAR ESTA LÍNEA
    Route::get('/{id}', [RestaurantController::class, 'show']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [RestaurantController::class, 'store']);
        Route::put('/{id}', [RestaurantController::class, 'update']);
        Route::delete('/{id}', [RestaurantController::class, 'destroy']);
        Route::get('/{id}/employees', [RestaurantController::class, 'getEmployees']);
        Route::get('/{id}/dashboard-stats', [RestaurantController::class, 'getDashboardStats']);
    });
});