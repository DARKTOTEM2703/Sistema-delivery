<?php


namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

    public function getOrder($id)
    {
        $order = Order::with(['items.product', 'restaurant', 'user'])
            ->where('id', $id)
            ->first();
        
        // Verificar si el usuario tiene permisos para ver este pedido
        if (!$order) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }
        
        // El usuario puede ver su propio pedido o el restaurante puede ver sus pedidos
        $userIsCustomer = Auth::id() === $order->user_id;
        $userIsRestaurant = Auth::user()->ownsRestaurant($order->restaurant_id);
        
        if (!$userIsCustomer && !$userIsRestaurant && !Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        return response()->json($order);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Verificar permisos - solo el restaurante puede actualizar
        if (!Auth::user()->ownsRestaurant($order->restaurant_id) && 
            !Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];
        
        $request->validate([
            'status' => 'required|string|in:' . implode(',', $validStatuses)
        ]);
        
        // Actualizar el estado
        $order->status = $request->status;
        
        // Registrar timestamps según el estado
        $timestampField = $request->status . '_at';
        if (Schema::hasColumn('orders', $timestampField)) {
            $order->$timestampField = now();
        }
        
        // Si está listo para entrega, calcular tiempo estimado
        if ($request->status === 'out_for_delivery' && !$order->estimated_delivery_time) {
            // Estimar 30 minutos desde ahora
            $order->estimated_delivery_time = now()->addMinutes(30);
        }
        
        $order->save();
        
        return response()->json($order);
    }

    public function cancelOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Verificar quién puede cancelar
        $userIsCustomer = Auth::id() === $order->user_id;
        $userIsRestaurant = Auth::user()->ownsRestaurant($order->restaurant_id);
        
        if (!$userIsCustomer && !$userIsRestaurant && !Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        // Solo se puede cancelar en ciertos estados
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'error' => 'No se puede cancelar el pedido en su estado actual'
            ], 400);
        }
        
        $order->status = 'cancelled';
        $order->cancelled_at = now();
        
        if ($request->has('cancel_reason')) {
            $order->cancel_reason = $request->cancel_reason;
        }
        
        $order->save();
        
        return response()->json($order);
    }
}
