<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('order_uuid')->references('uuid')->on('orders');
            $table->foreignUuid('product_uuid')->references('uuid')->on('products');
            $table->integer('quantity');
            $table->decimal('price_at_order', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
