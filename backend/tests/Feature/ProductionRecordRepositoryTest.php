<?php

use App\Models\ProductionRecordModel;
use Tpm\Production\ProductionRecord;
use Tpm\Production\ProductionRecordRepository;
use Tpm\Shared\ProductionRecordId;

it('resolves the port to the eloquent adapter and round-trips an entity', function () {
    $repository = app(ProductionRecordRepository::class);

    $row = ProductionRecordModel::factory()->create([
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);

    $entity = $repository->findById(new ProductionRecordId($row->id));

    expect($entity)->toBeInstanceOf(ProductionRecord::class)
        ->and($entity->producedUnits())->toBe(800)
        ->and($entity->goodUnits())->toBe(780)
        ->and($entity->plannedTime()->seconds)->toBe(8 * 3600);
});
