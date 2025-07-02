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
        // SOLO crear la tabla review_votes, NO modificar reviews
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_helpful'); // true = útil, false = no útil
            $table->timestamps();

            // Un usuario solo puede votar una vez por review
            $table->unique(['review_id', 'user_id']);
        });

        // Verificar si necesitamos agregar campos faltantes a reviews
        // Solo agregar si NO existen
        if (!Schema::hasColumn('reviews', 'food_rating')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->tinyInteger('food_rating')->unsigned()->nullable()->after('rating');
                $table->tinyInteger('service_rating')->unsigned()->nullable()->after('food_rating');
                $table->tinyInteger('delivery_rating')->unsigned()->nullable()->after('service_rating');
                $table->json('images')->nullable()->after('comment');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_votes');

        // Revertir cambios en reviews solo si existen
        if (Schema::hasColumn('reviews', 'food_rating')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn(['food_rating', 'service_rating', 'delivery_rating', 'images']);
            });
        }
    }
};
