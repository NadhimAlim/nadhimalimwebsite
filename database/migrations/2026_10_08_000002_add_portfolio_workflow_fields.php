<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('message');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('link');
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('level');
        });

        $order = 0;
        foreach (DB::table('projects')->orderByDesc('created_at')->orderBy('id')->pluck('id') as $id) {
            DB::table('projects')->where('id', $id)->update(['sort_order' => $order++]);
        }

        $order = 0;
        foreach (DB::table('skills')->orderBy('type')->orderBy('name')->orderBy('id')->pluck('id') as $id) {
            DB::table('skills')->where('id', $id)->update(['sort_order' => $order++]);
        }
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
};
