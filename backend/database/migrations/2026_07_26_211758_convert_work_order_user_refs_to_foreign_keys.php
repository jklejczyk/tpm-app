<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE work_orders ALTER COLUMN reported_by TYPE bigint USING reported_by::bigint');
        DB::statement('ALTER TABLE work_orders ALTER COLUMN assigned_to TYPE bigint USING assigned_to::bigint');

        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreign('reported_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['reported_by']);
            $table->dropForeign(['assigned_to']);
        });

        DB::statement('ALTER TABLE work_orders ALTER COLUMN reported_by TYPE varchar(255) USING reported_by::varchar');
        DB::statement('ALTER TABLE work_orders ALTER COLUMN assigned_to TYPE varchar(255) USING assigned_to::varchar');
    }
};
