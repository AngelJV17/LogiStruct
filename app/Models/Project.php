<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use HasFactory, SoftDeletes;
    protected $appends = ['cover_url'];

    protected $fillable = [
        'project_code', 'project_name', 'short_name',
        'type_id', 'status_id',
        'contractual_amount', 'projected_amount',
        'start_date', 'end_date_contractual', 'end_date_real',
        'department_id', 'province_id', 'district_id', 'address',
        'cover_image', 'consortium_id', 'company_id',
    ];

    protected $casts = [
        'start_date'           => 'date',
        'end_date_contractual' => 'date',
        'end_date_real'        => 'date',
        'contractual_amount'   => 'decimal:2',
        'projected_amount'     => 'decimal:2',
    ];

    // --- RELACIONES ---

    public function type(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(GlobalParameter::class, 'status_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function consortium(): BelongsTo
    {
        return $this->belongsTo(Consortium::class);
    }

    // Ubigeo
    public function department(): BelongsTo
    {
        return $this->belongsTo(UbigeoPeruDepartment::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(UbigeoPeruProvince::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(UbigeoPeruDistrict::class);
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->cover_image
                ? Storage::url($this->cover_image)
                : null,
        );
    }
}
