<?php

namespace App\Models;

use Database\Factories\MachineModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 */
class MachineModel extends Model
{
    /** @use HasFactory<MachineModelFactory> */
    use HasFactory;

    protected $table = 'machines';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
    ];
}
