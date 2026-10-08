<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('subtotal_amount')->default(0)->after('quantity');
            $table->unsignedBigInteger('shipping_amount')->default(0)->after('subtotal_amount');
            $table->string('shipping_method', 80)->nullable()->after('shipping_amount');
        });

        Schema::create('marketplace_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_order_id')->constrained('marketplace_orders')->cascadeOnDelete();
            $table->foreignId('marketplace_product_id')->nullable()->constrained('marketplace_products')->nullOnDelete();
            $table->string('product_name');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_order_items');
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal_amount', 'shipping_amount', 'shipping_method']);
        });
    }
};
