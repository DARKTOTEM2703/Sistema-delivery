<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla para items de inventario
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('category', 50);
            $table->decimal('current_stock', 10, 2);
            $table->string('unit', 20);
            $table->decimal('min_stock', 10, 2);
            $table->timestamp('last_restock_at')->nullable();
            $table->timestamps();
        });

        // Tabla para logs de inventario
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action', 50); // restock, consume, adjust, initial_stock
            $table->decimal('quantity', 10, 2);
            $table->decimal('previous_stock', 10, 2)->nullable();
            $table->decimal('new_stock', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('inventory_items');
    }
};