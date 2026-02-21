<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'ruc',
        'name',
        'email',
        'phone',
        'address',
        'legal_representative',
        'representative_dni',
        'representative_phone',
        'issues_payment_order',
        'url_logo',
    ];

    protected $casts = [
        'issues_payment_order' => 'boolean',
    ];

    /**
     * Una empresa puede tener muchos proyectos directos
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Una empresa puede participar en muchos consorcios
     */
    public function consortiums(): BelongsToMany
    {
        return $this->belongsToMany(Consortium::class, 'consortium_company')
            ->using(ConsortiumCompany::class)
            ->withPivot('participation_percentage')
            ->withTimestamps();
    }
}
