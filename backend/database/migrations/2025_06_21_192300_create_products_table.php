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
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->string('category');
            $table->decimal('rating', 3, 1)->default(5.0);
            $table->string('time')->nullable();
            $table->string('servings')->nullable();
            $table->foreignId('restaurant_id')->after('id')->constrained()->onDelete('cascade');
            $table->boolean('is_available')->default(true);
            $table->integer('preparation_time')->nullable(); // minutos
            $table->string('allergens')->nullable(); // gluten, lactosa, etc.
            $table->timestamps();
        });

        // Actualizar tabla orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->after('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_person_id')->nullable()->after('restaurant_id')->constrained('users')->onDelete('set null');
            $table->string('order_type')->default('delivery'); // delivery, pickup
            $table->text('special_instructions')->nullable();
            $table->decimal('subtotal', 10, 2)->after('total');
            $table->decimal('tax_amount', 8, 2)->default(0);
            $table->decimal('tip_amount', 8, 2)->default(0);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
        });

        // Actualizar tabla users para incluir más información
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'avatar', 'is_active', 'last_login_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropForeign(['delivery_person_id']);
            $table->dropColumn([
                'restaurant_id', 'delivery_person_id', 'order_type', 
                'special_instructions', 'subtotal', 'tax_amount', 
                'tip_amount', 'accepted_at', 'prepared_at', 'picked_up_at'
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn(['restaurant_id', 'is_available', 'preparation_time', 'allergens']);
        });
    }
};