<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Worker extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * El UUID es la clave de búsqueda en las rutas para mayor seguridad.
     */
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'uuid', 'document_type_id', 'document_number', 'first_name',
        'last_name_paternal', 'last_name_maternal', 'birth_date',
        'gender_id', 'phone', 'email', 'address', 'worker_type_id',
        'position_id', 'project_id', 'company_id', 'daily_salary',
        'monthly_salary', 'payment_type_id', 'bank_id',
        'pension_system_id', 'bank_account', 'cci', 'cuspp',
        'hire_date', 'photo_path', 'is_active',
    ];

    protected $casts = [
        'daily_salary'   => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'is_active'      => 'boolean',
        'birth_date'     => 'date:Y-m-d',
        'hire_date'      => 'date:Y-m-d',
    ];

    protected $appends = ['full_name', 'photo_url'];

    /**
     * Define qué columna se usa para el Route Model Binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // --- ACCESSORS (Lógica de Presentación) ---

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name_paternal} {$this->last_name_maternal}");
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    // --- RELATIONS (CamelCase según PSR) ---

    /**
     * Relación con los parámetros globales (DNI, CE, etc.)
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'document_type_id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'gender_id');
    }

    public function workerType(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'worker_type_id');
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'payment_type_id');
    }

    /**
     * Relaciones con entidades maestras.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class)->withDefault(['name' => 'Sin Cargo']);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withDefault(['name' => 'Empresa No Definida']);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withDefault(['name' => 'Oficina Central']);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class)->withDefault(['name' => 'No Asignado']);
    }

    public function pensionSystem(): BelongsTo
    {
        return $this->belongsTo(PensionSystem::class, 'pension_system_id')->withDefault(['name' => 'Ninguno']);
    }

    // --- RELATIONS (HasMany / BelongsToMany) ---

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function safetyTalks(): BelongsToMany
    {
        return $this->belongsToMany(SafetyTalk::class, 'safety_talk_worker');
    }
}
