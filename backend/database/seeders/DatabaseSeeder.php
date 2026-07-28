<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkOrderModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Tpm\WorkOrder\WorkOrderReason;
use Tpm\WorkOrder\WorkOrderStatus;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $reporter = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(MachineSeeder::class);

        $this->call(ProductionRecordSeeder::class);

        $technician = User::factory()->technician()->create([
            'name' => 'Demo Technician',
            'email' => 'technician@example.com',
        ]);

        WorkOrderModel::factory()->create([
            'machine_id' => 'press-01',
            'status' => WorkOrderStatus::Resolved->value,
            'reason' => WorkOrderReason::Breakdown->value,
            'reported_by' => $reporter->id,
            'assigned_to' => $technician->id,
            'reported_at' => '2026-01-01 10:00:00',
            'resolved_at' => '2026-01-01 12:00:00',
            'resolution' => 'Replaced worn belt, machine restarted and verified.',
        ]);
    }
}
