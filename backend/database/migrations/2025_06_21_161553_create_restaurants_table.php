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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('slug')->unique(); // URL amigable
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('address');
            $table->string('phone');
            $table->string('email');
            $table->string('category'); // italiana, mexicana, china, etc.
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->integer('delivery_time_min')->default(30);
            $table->integer('delivery_time_max')->default(60);
            $table->decimal('minimum_order', 8, 2)->default(0);
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->integer('total_reviews')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('accepts_cash')->default(true);
            $table->boolean('accepts_card')->default(true);
            $table->json('business_hours'); // Horarios de atención
            $table->json('delivery_zones'); // Zonas de entrega
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
