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
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Dinero en caja
            $table->decimal('opening_cash', 10, 2); // Dinero inicial
            $table->decimal('closing_cash', 10, 2)->nullable(); // Dinero final contado
            $table->decimal('expected_cash', 10, 2)->nullable(); // Dinero esperado según sistema
            $table->decimal('cash_difference', 10, 2)->nullable(); // Diferencia (faltante/sobrante)
            
            // Tiempos
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            
            // Notas
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            
            // Estado
            $table->enum('status', ['open', 'closed', 'suspended'])->default('open');
            
            $table->timestamps();
            
            // Índices
            $table->index(['cashier_id', 'status']);
            $table->index(['cash_register_id', 'opened_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');
    }
};
