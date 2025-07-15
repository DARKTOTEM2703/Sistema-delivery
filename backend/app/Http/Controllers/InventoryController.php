<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request, $restaurantId)
    {
        // Obtener los ítems de inventario
        $items = InventoryItem::where('restaurant_id', $restaurantId)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
            
        return response()->json($items);
    }
    
    public function store(Request $request, $restaurantId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|numeric|min:0'
        ]);
        
        $validated['restaurant_id'] = $restaurantId;
        $validated['last_restock_at'] = now();
        
        DB::beginTransaction();
        
        try {
            $item = InventoryItem::create($validated);
            
            // Registrar log de inventario inicial
            InventoryLog::create([
                'inventory_item_id' => $item->id,
                'user_id' => Auth::id(),
                'action' => 'initial_stock',
                'quantity' => $validated['current_stock'],
                'previous_stock' => 0,
                'new_stock' => $validated['current_stock'],
                'notes' => 'Inventario inicial'
            ]);
            
            DB::commit();
            return response()->json($item, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear el ítem de inventario: ' . $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|numeric|min:0'
        ]);
        
        $previousStock = $item->current_stock;
        
        DB::beginTransaction();
        
        try {
            // Si hay un cambio en el stock, crear un log
            if ($validated['current_stock'] != $previousStock) {
                InventoryLog::create([
                    'inventory_item_id' => $item->id,
                    'user_id' => Auth::id(),
                    'action' => 'adjust',
                    'quantity' => $validated['current_stock'] - $previousStock,
                    'previous_stock' => $previousStock,
                    'new_stock' => $validated['current_stock'],
                    'notes' => 'Ajuste manual de inventario'
                ]);
                
                $validated['last_restock_at'] = now();
            }
            
            $item->update($validated);
            
            DB::commit();
            return response()->json($item);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el ítem de inventario'], 500);
        }
    }
    
    public function restock(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);
        
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string'
        ]);
        
        $previousStock = $item->current_stock;
        $newStock = $previousStock + $validated['quantity'];
        
        DB::beginTransaction();
        
        try {
            // Actualizar el stock
            $item->update([
                'current_stock' => $newStock,
                'last_restock_at' => now()
            ]);
            
            // Crear log
            InventoryLog::create([
                'inventory_item_id' => $item->id,
                'user_id' => Auth::id(),
                'action' => 'restock',
                'quantity' => $validated['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'notes' => $validated['notes'] ?? 'Reabastecimiento de inventario'
            ]);
            
            DB::commit();
            return response()->json($item);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al reabastecer el ítem de inventario'], 500);
        }
    }
}
