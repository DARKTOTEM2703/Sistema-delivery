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
        // Tabla de roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, owner, manager, cook, delivery, waiter, customer
            $table->string('display_name'); // Nombre legible
            $table->text('description');
            $table->json('permissions'); // Array de permisos
            $table->timestamps();
        });

        // Tabla pivot usuario-rol-restaurante
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            
            // Un usuario puede tener diferentes roles en diferentes restaurantes
            $table->unique(['user_id', 'role_id', 'restaurant_id']);
        });

        // Tabla para configuraciones específicas de empleados
        Schema::create('employee_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->decimal('hourly_rate', 8, 2)->nullable(); // Para repartidores
            $table->decimal('commission_rate', 5, 2)->nullable(); // % de comisión
            $table->json('work_schedule')->nullable(); // Horario de trabajo
            $table->boolean('can_receive_orders')->default(true);
            $table->string('status')->default('available'); // available, busy, offline
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_settings');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
    }
};