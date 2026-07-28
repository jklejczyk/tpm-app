<?php

namespace App\Models;

use Database\Factories\WorkOrderModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $machine_id
 * @property string $status
 * @property string $reason
 * @property int $reported_by
 * @property int|null $assigned_to
 * @property string|null $resolution
 * @property string|null $hold_reason
 * @property Carbon $reported_at
 * @property Carbon|null $resolved_at
 * @property-read User $reporter
 * @property-read User|null $assignee
 * @property-read MachineModel $machine
 */
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
        'resolved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return BelongsTo<MachineModel, $this>
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(MachineModel::class, 'machine_id', 'id');
    }
}
