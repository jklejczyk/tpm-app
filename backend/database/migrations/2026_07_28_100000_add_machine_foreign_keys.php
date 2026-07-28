<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table): void {
            $table->foreign('machine_id')->references('id')->on('machines')->restrictOnDelete();
        });

        Schema::table('production_records', function (Blueprint $table): void {
            $table->foreign('machine_id')->references('id')->on('machines')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table): void {
            $table->dropForeign(['machine_id']);
        });

        Schema::table('production_records', function (Blueprint $table): void {
            $table->dropForeign(['machine_id']);
        });
    }
};
