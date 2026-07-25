<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->index(['created_at', 'id']);
            $table->index(['status', 'id']);
            $table->index(['machine_id', 'id']);
            $table->index(['reason', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'id']);
            $table->dropIndex(['status', 'id']);
            $table->dropIndex(['machine_id', 'id']);
            $table->dropIndex(['reason', 'id']);
        });
    }
};
