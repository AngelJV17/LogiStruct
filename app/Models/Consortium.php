<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Consortium extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruc', 
        'name', 
        'url_logo', 
        'legal_representative',
        'representative_dni', 
        'representative_email', 
        'representative_phone',
    ];

    // Relación con las empresas que lo integran
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'consortium_company')
            ->using(ConsortiumCompany::class) // Usamos el pivot profesional
            ->withPivot('participation_percentage')
            ->withTimestamps();
    }
}
