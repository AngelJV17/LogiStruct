<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;
    protected $appends = ['logo_url'];

    protected $fillable = [
        'ruc',
        'name',
        'email',
        'phone',
        'address',
        'url_logo',
        'issues_payment_order',
        'legal_representative',
        'representative_dni',
        'representative_phone',
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

    protected function logoUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->url_logo
                ? Storage::url($this->url_logo)
                : asset('img/default-logo.png');
        });
    }
}
