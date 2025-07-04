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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            
            // Numeración fiscal
            $table->string('invoice_number')->unique(); // FAC-2025-001
            $table->string('fiscal_series')->nullable(); // Serie fiscal
            $table->string('fiscal_number')->nullable(); // Número fiscal
            
            // Datos del cliente (requeridos para factura)
            $table->string('customer_name');
            $table->string('customer_rfc')->nullable(); // RFC en México
            $table->text('customer_address');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Montos
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            // Información fiscal
            $table->decimal('tax_rate', 5, 4)->default(0.1600); // 16% IVA México
            $table->string('currency', 3)->default('MXN');
            $table->enum('payment_method', ['efectivo', 'tarjeta', 'transferencia']);
            
            // Estados
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Archivos
            $table->string('pdf_path')->nullable(); // Ruta del PDF generado
            $table->string('xml_path')->nullable(); // XML fiscal (México)
            
            $table->timestamps();
            
            // Índices
            $table->index(['restaurant_id', 'status']);
            $table->index(['customer_id', 'issued_at']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
