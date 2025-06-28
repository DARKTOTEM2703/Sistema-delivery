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
        // Esta migración se ejecuta DESPUÉS de que restaurants ya existe
        // Solo verifica y corrige si hay algún problema residual

        // 1. Verificar que la tabla products tenga la foreign key correcta
        if (!Schema::hasColumn('products', 'restaurant_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('restaurant_id')->after('id')->constrained()->onDelete('cascade');
            });
        }

        // 2. Asegurar que orders tenga restaurant_id (desde update_employee_settings)
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'restaurant_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('restaurant_id')->after('user_id');
                $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            });
        }

        // 3. Verificar que users tenga los campos adicionales
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->nullable()->after('email');
                $table->text('address')->nullable();
                $table->string('avatar')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir solo si es necesario
    }
};
