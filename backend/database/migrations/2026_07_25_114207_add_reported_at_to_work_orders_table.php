<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->timestamp('reported_at')->nullable()->after('reported_by');
        });

        DB::table('work_orders')
            ->whereNull('reported_at')
            ->update(['reported_at' => DB::raw('created_at')]);

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'id']);
            $table->index(['reported_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['reported_at', 'id']);
            $table->index(['created_at', 'id']);
            $table->dropColumn('reported_at');
        });
    }
};
