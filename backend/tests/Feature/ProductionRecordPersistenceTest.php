<?php

use App\Models\ProductionRecordModel;

it('persists and reads back a production record', function () {
    $record = ProductionRecordModel::factory()->create([
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);

    $fresh = ProductionRecordModel::query()->find($record->id);

    expect($fresh->produced_units)->toBe(800)
        ->and($fresh->defective_units)->toBe(20)
        ->and($fresh->ideal_cycle_time)->toBe(30);
});
