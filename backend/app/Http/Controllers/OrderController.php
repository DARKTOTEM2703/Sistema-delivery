<?php


namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'address' => 'required|string',
            'phone' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // Crear la orden
            $order = Order::create([
                'user_id' => Auth::id(),
                'restaurant_id' => $validated['restaurant_id'],  // ✅ AGREGAR
                'total' => $validated['total'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'payment_method' => $validated['payment_method'],
                'status' => 'pending'
            ]);

            // Crear los items de la orden
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity']  // ✅ AGREGAR
                ]);
            }

            DB::commit();

            return response()->json([
                'order' => $order->load('items.product'),
                'message' => 'Pedido creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Error al crear el pedido'
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($order);
    }

    public function getRestaurantOrders(Request $request, $restaurantId)
    {
        // Verificar permiso
        $restaurant = Restaurant::findOrFail($restaurantId);

        // Solo permitir al dueño o empleados ver los pedidos
        if (!Auth::user()->ownsRestaurant($restaurantId) &&
            !Auth::user()->worksAtRestaurant($restaurantId)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Obtener los pedidos
        $orders = Order::where('restaurant_id', $restaurantId)
            ->with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Verificar permiso
        if (!Auth::user()->ownsRestaurant($order->restaurant_id) &&
            !Auth::user()->worksAtRestaurant($order->restaurant_id)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled'
        ]);

        // Actualizar estado
        $order->status = $request->status;

        // Actualizar timestamps según el estado
        switch ($request->status) {
            case 'confirmed':
                $order->accepted_at = now();
                break;
            case 'preparing':
                $order->prepared_at = now();
                break;
            case 'out_for_delivery':
                $order->picked_up_at = now();
                break;
        }

        $order->save();

        return response()->json($order);
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Verificar permiso
        $userIsOwner = Auth::user()->ownsRestaurant($order->restaurant_id);
        $userIsCustomer = Auth::id() === $order->user_id;

        if (!$userIsOwner && !$userIsCustomer) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Solo se pueden cancelar pedidos que no estén entregados
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'error' => 'No se puede cancelar un pedido ya entregado o cancelado'
            ], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json($order);
    }
}
