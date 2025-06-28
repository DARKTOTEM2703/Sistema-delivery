<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'role' => 'sometimes|string|in:customer,owner,delivery',
            'restaurant_id' => 'sometimes|exists:restaurants,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'last_login_at' => now()
        ]);

        // Asignar rol (por defecto customer)
        $roleName = $request->role ?? 'customer';
        $restaurantId = $request->restaurant_id;

        // Validar que si es un rol de empleado, se especifique el restaurante
        if (in_array($roleName, ['owner', 'manager', 'cook', 'delivery', 'waiter']) && !$restaurantId) {
            return response()->json([
                'error' => 'Restaurant ID is required for employee roles'
            ], 400);
        }

        $user->assignRole($roleName, $restaurantId);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $token,
            'message' => 'Usuario registrado exitosamente'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Tu cuenta está desactivada. Contacta al administrador.'],
            ]);
        }

        // Actualizar último login
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Cargar roles y restaurantes
        $user->load(['roles', 'ownedRestaurants', 'employeeRestaurants']);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login exitoso'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['roles', 'ownedRestaurants', 'employeeRestaurants']);
        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'user' => $user,
            'message' => 'Perfil actualizado exitosamente'
        ]);
    }

    public function switchRole(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string',
            'restaurant_id' => 'nullable|exists:restaurants,id'
        ]);

        $user = $request->user();
        $roleName = $request->role_name;
        $restaurantId = $request->restaurant_id;

        if (!$user->hasRole($roleName, $restaurantId)) {
            return response()->json([
                'error' => 'No tienes permisos para este rol'
            ], 403);
        }

        // Aquí podrías implementar lógica para cambiar el contexto actual del usuario
        // Por ejemplo, guardar en sesión o en el token el rol activo

        return response()->json([
            'message' => 'Rol cambiado exitosamente',
            'active_role' => $roleName,
            'restaurant_id' => $restaurantId
        ]);
    }
}
