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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->string('category');
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->string('time')->nullable();
            $table->string('servings')->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('preparation_time')->nullable(); // minutos
            $table->string('allergens')->nullable(); // gluten, lactosa, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};