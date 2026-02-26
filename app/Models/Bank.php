<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'ruc',
    ];

    /**
     * Trabajadores que tienen cuenta en este banco.
     */
    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }
}
