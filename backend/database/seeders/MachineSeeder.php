<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Tpm\Machine\Machine;
use Tpm\Machine\MachineRepository;
use Tpm\Shared\MachineId;

class MachineSeeder extends Seeder
{
    public function run(MachineRepository $repository): void
    {
        $machines = [
            'press-01' => 'Hydraulic Press #1',
            'press-02' => 'Hydraulic Press #2',
            'cnc-01' => 'CNC Mill #1',
            'weld-01' => 'Welding Robot #1',
            'conv-01' => 'Assembly Conveyor #1',
        ];

        foreach ($machines as $id => $name) {
            if ($repository->byId(new MachineId($id)) === null) {
                $repository->save(Machine::register(new MachineId($id), $name));
            }
        }
    }
}
