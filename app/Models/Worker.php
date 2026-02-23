<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Worker extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'document_type_id',
        'document_number',
        'first_name',
        'last_name_paternal',
        'last_name_maternal',
        'birth_date',
        'gender_id',
        'phone',
        'email',
        'address',
        'worker_type_id',
        'position_id',
        'project_id',
        'company_id',
        'daily_salary',
        'monthly_salary',
        'payment_type_id',
        'photo_path',
        'is_active',
    ];

    /**
     * Boot function para generar el UUID automáticamente al crear un trabajador.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // --- ACCESSORS (Atributos virtuales para el Frontend) ---

    /**
     * Combina nombres y apellidos. Uso: $worker->full_name
     */
    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name_paternal} {$this->last_name_maternal}";
    }

    /**
     * Retorna la URL de la foto o una por defecto.
     */
    public function getPhotoUrlAttribute(): string
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : asset('images/default-avatar.png');
    }

    // --- RELACIONES ---

    /**
     * Tipo de Documento (DNI, CE, etc.) desde global_parameters
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'document_type_id');
    }

    /**
     * Clasificación (Obrero, Oficina, etc.) desde global_parameters
     */
    public function workerType(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'worker_type_id');
    }

    /**
     * Cargo específico (Maestro, Operario, etc.) desde global_parameters
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'position_id');
    }

    /**
     * Tipo de pago (Semanal, Quincenal, Mensual)
     */
    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'payment_type_id');
    }

    /**
     * Empresa (del consorcio) que lo contrata
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Proyecto/Obra asignada
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function currentProject()
    {
        return $this->hasOne(ProjectWorker::class)->where('is_active', true);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function safetyTalks()
    {
        return $this->belongsToMany(SafetyTalk::class, 'safety_talk_worker');
    }
}
