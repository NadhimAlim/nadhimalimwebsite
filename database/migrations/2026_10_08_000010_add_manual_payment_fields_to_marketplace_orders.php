<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->string('payment_method', 30)->nullable()->after('payment_status');
            $table->string('payment_proof_path')->nullable()->after('payment_method');
            $table->text('payment_note')->nullable()->after('payment_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_proof_path', 'payment_note']);
        });
    }
};
