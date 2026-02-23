<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SafetyTalk extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'project_id',
        'date',
        'topic',
        'description',
        'instructor_name',
        'evidence_path',
    ];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Worker::class, 'safety_talk_worker')
            ->withPivot('signed');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
