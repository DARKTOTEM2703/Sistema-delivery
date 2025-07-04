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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // ORD-2025-001
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');

            // CLIENTE (puede ser null para ventas sin registro)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();

            // TIPO DE ORDEN
            $table->enum('order_type', ['delivery', 'pickup', 'pos'])->default('delivery');
            $table->enum('status', [
                'pending',
                'confirmed',
                'preparing',
                'ready',
                'out_for_delivery',
                'delivered',
                'completed',
                'cancelled'
            ])->default('pending');

            // ENTREGA (solo para delivery)
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->foreignId('delivery_person_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('delivered_at')->nullable();

            // MONTOS
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('tip_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // PAGO
            $table->enum('payment_method', ['efectivo', 'tarjeta', 'transferencia', 'pos_cash', 'pos_card']);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            // POS ESPECÍFICO
            $table->foreignId('cashier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('receipt_number')->nullable(); // Para tickets POS
            $table->decimal('cash_received', 10, 2)->nullable(); // Dinero recibido
            $table->decimal('change_given', 10, 2)->nullable(); // Cambio dado

            // OTROS
            $table->text('special_instructions')->nullable();
            $table->json('preparation_notes')->nullable(); // Notas de cocina
            $table->timestamp('estimated_delivery_time')->nullable();

            $table->timestamps();

            // Índices para performance
            $table->index(['restaurant_id', 'created_at']);
            $table->index(['delivery_person_id', 'status']);
            $table->index(['order_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
