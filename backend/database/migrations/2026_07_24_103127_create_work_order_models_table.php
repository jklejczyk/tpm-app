<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('machine_id');
            $table->string('status');
            $table->string('reason');
            $table->string('reported_by');
            $table->string('assigned_to')->nullable();
            $table->text('resolution')->nullable();
            $table->text('hold_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
