<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PensionSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // AFP o ONP
    ];

    /**
     * Trabajadores afiliados a este sistema de pensiones.
     */
    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class, 'pension_system_id');
    }
}
