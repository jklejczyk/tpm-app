<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderModel extends Model
{
    /** @use HasFactory<\Database\Factories\WorkOrderModelFactory> */
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
        'assigned_to',
        'resolution',
        'hold_reason',
    ];
}
