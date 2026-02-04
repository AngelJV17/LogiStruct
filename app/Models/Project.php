<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code', 'project_name', 'short_name', 'type_id', 'status_id',
        'contractual_amount', 'projected_amount', 'start_date', 
        'end_date_contractual', 'end_date_real', 'department_id', 
        'province_id', 'district_id', 'address', 'cover_image', 
        'consortium_id', 'company_id'
    ];

    // --- RELACIONES ---

    public function type(): BelongsTo {
        return $this->belongsTo(GlobalParameter::class, 'type_id');
    }

    public function status(): BelongsTo {
        return $this->belongsTo(GlobalParameter::class, 'status_id');
    }

    public function company(): BelongsTo {
        return $this->belongsTo(Company::class);
    }

    public function consortium(): BelongsTo {
        return $this->belongsTo(Consortium::class);
    }

    public function workers(): HasMany {
        return $this->hasMany(Worker::class);
    }

    /* public function investments(): HasMany {
        return $this->hasMany(Investment::class);
    } */

    // --- LÓGICA DE NEGOCIO (ACCESSORS) ---

    /**
     * Obtiene el nombre del dueño (Consorcio o Empresa) automáticamente.
     * Uso: $project->owner_name
     */
    public function getOwnerNameAttribute(): string {
        if ($this->consortium_id) {
            return $this->consortium->name;
        }
        return $this->company ? $this->company->name : 'Sin asignar';
    }

    /**
     * Calcula el saldo por ejecutar para la Curva S.
     */
    public function getBalanceToExecuteAttribute(): float {
        return $this->contractual_amount - $this->projected_amount;
    }
}
