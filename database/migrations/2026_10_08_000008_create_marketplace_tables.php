<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 100)->nullable();
            $table->text('description');
            $table->unsignedBigInteger('price');
            $table->unsignedInteger('stock')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('marketplace_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_product_id')->nullable()->constrained('marketplace_products')->nullOnDelete();
            $table->string('public_token', 64)->unique();
            $table->string('order_id', 64)->unique();
            $table->string('product_name');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('total_amount');
            $table->string('customer_name', 120);
            $table->string('customer_email', 190)->nullable();
            $table->string('customer_phone', 30);
            $table->text('shipping_address');
            $table->string('status', 30)->default('awaiting_payment')->index();
            $table->string('payment_status', 30)->default('pending')->index();
            $table->string('snap_token')->nullable();
            $table->text('redirect_url')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_type', 80)->nullable();
            $table->json('notification_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_orders');
        Schema::dropIfExists('marketplace_products');
    }
};
