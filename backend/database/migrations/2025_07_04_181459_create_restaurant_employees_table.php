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
        Schema::create('restaurant_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('hired_by')->constrained('users')->onDelete('cascade'); // Quien lo contrató
            
            // Rol específico del empleado
            $table->enum('position', [
                'manager',        // Gerente
                'cashier',        // Cajero
                'cook',          // Cocinero
                'waiter',        // Mesero
                'delivery',      // Repartidor
                'cleaner'        // Limpieza
            ]);
            
            // Información laboral
            $table->decimal('hourly_wage', 8, 2)->nullable(); // Salario por hora
            $table->date('hired_at');
            $table->date('terminated_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Permisos específicos
            $table->json('permissions')->nullable(); // Permisos específicos del empleado
            $table->json('work_schedule')->nullable(); // Horarios de trabajo
            
            // Notas
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Un usuario solo puede tener una posición activa por restaurante
            $table->unique(['restaurant_id', 'user_id', 'is_active']);
            
            // Índices
            $table->index(['restaurant_id', 'position', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_employees');
    }
};
