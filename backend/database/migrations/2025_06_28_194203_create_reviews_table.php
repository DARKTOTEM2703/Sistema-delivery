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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->tinyInteger('rating')->unsigned(); // 1-5 estrellas
            $table->text('comment')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->text('response')->nullable(); // Respuesta del restaurante
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['restaurant_id', 'rating']);
            $table->index(['user_id', 'created_at']);
            
            // Un usuario solo puede reseñar una vez por pedido
            $table->unique(['user_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};