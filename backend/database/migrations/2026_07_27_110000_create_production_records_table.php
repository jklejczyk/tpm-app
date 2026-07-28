<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_records', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('machine_id');
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->unsignedInteger('produced_units');
            $table->unsignedInteger('defective_units');
            $table->unsignedInteger('ideal_cycle_time');
            $table->timestamps();

            $table->index(['machine_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_records');
    }
};
