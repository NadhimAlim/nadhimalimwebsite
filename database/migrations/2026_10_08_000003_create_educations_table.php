<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->string('level')->unique();
            $table->string('institution')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->string('description', 500)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('educations')->insert([
            ['level' => 'SD', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 'SMP', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 'SMA', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 'Kuliah', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
