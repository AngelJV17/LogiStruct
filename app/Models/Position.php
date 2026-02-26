<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department',
        'is_active',
    ];

    /**
     * Obtener los trabajadores que ocupan este cargo.
     */
    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }
}
