<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quien hizo el movimiento
            
            // Tipo de movimiento
            $table->enum('type', [
                'sale',           // Venta
                'refund',         // Devolución  
                'expense',        // Gasto
                'deposit',        // Depósito de dinero
                'withdrawal',     // Retiro de dinero
                'adjustment'      // Ajuste manual
            ]);
            
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->string('reference')->nullable(); // Número de recibo, factura, etc.
            $table->json('metadata')->nullable(); // Datos adicionales
            
            $table->timestamps();
            
            // Índices
            $table->index(['cash_shift_id', 'type']);
            $table->index(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
