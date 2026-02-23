<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlobalParameter extends Model
{
    use HasFactory;
    
    protected $fillable = ['group', 'name', 'description', 'parent_id', 'level', 'is_active'];

    // Relación para obtener el padre
    public function parent() {
        return $this->belongsTo(GlobalParameter::class, 'parent_id');
    }

    // Relación para obtener los hijos (sub-parámetros)
    public function children() {
        return $this->hasMany(GlobalParameter::class, 'parent_id');
    }

    // Scope para filtrar por grupo fácilmente
    public function scopeByGroup($query, $group) {
        return $query->where('group', $group);
    }
}
