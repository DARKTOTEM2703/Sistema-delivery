<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RestaurantController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas de productos (públicas)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/search', [ProductController::class, 'search']);

// Rutas de restaurantes (públicas)
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
Route::get('/restaurants/categories', [RestaurantController::class, 'getCategories']);
Route::get('/restaurants/{id}/products', [ProductController::class, 'getByRestaurant']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Usuario
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Pedidos
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // Gestión de restaurantes (solo propietarios)
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::put('/restaurants/{id}', [RestaurantController::class, 'update']);
    Route::delete('/restaurants/{id}', [RestaurantController::class, 'destroy']);
    
    // Dashboard del restaurante
    Route::get('/restaurants/{id}/employees', [RestaurantController::class, 'getEmployees']);
    Route::get('/restaurants/{id}/dashboard', [RestaurantController::class, 'getDashboardStats']);
});

// Ruta de fallback para errores 404
Route::fallback(function(){
    return response()->json([
        'message' => 'Ruta no encontrada. Verifica la URL y el método HTTP.'
    ], 404);
});