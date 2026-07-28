<?php

namespace App\Models;

use Database\Factories\ProductionRecordModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $machine_id
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property int $produced_units
 * @property int $defective_units
 * @property int $ideal_cycle_time
 * @property-read MachineModel $machine
 */
class ProductionRecordModel extends Model
{
    /** @use HasFactory<ProductionRecordModelFactory> */
    use HasFactory;

    protected $table = 'production_records';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'machine_id',
        'period_start',
        'period_end',
        'produced_units',
        'defective_units',
        'ideal_cycle_time',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'produced_units' => 'integer',
            'defective_units' => 'integer',
            'ideal_cycle_time' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<MachineModel, $this>
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(MachineModel::class, 'machine_id', 'id');
    }
}
