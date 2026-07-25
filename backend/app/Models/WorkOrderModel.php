<?php

namespace App\Models;

use Database\Factories\WorkOrderModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderModel extends Model
{
    /** @use HasFactory<WorkOrderModelFactory> */
    use HasFactory;

    protected $table = 'work_orders';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'machine_id',
        'status',
        'reason',
        'reported_by',
        'reported_at',
        'assigned_to',
        'resolution',
        'hold_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
        ];
    }
}
