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
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Caja 1, Caja Principal, Mostrador
            $table->string('device_id')->nullable(); // ID del dispositivo/terminal
            $table->string('location')->nullable(); // Ubicación física
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Configuraciones específicas
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};