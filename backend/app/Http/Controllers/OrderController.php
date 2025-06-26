<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customerInfo' => 'required',
            'customerInfo.name' => 'required|string',
            'customerInfo.address' => 'required|string',
            'customerInfo.phone' => 'required|string',
            'customerInfo.paymentMethod' => 'required|string',
            'items' => 'required|array',
            'items.*.id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => Auth::id(), // Será null si el usuario no está autenticado
                'total' => $validated['total'],
                'status' => 'pending',
                'address' => $validated['customerInfo']['address'],
                'phone' => $validated['customerInfo']['phone'],
                'payment_method' => $validated['customerInfo']['paymentMethod'],
                'payment_status' => 'pending'
            ]);

            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'message' => 'Pedido creado correctamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        return response()->json($order);
    }

    public function userOrders()
    {
        $orders = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }
}