<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'worker_id',
        'project_id',
        'date', 'check_in',
        'check_out', 'status_id',
        'hours_worked',
        'overtime_hours',
        'latitude',
        'longitude',
        'observation',
    ];

    // Scope para filtrar por rango de fechas (útil para planillas semanales)
    public function scopeBetweenDates(Builder $query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function status()
    {
        return $this->belongsTo(GlobalParameter::class, 'status_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
