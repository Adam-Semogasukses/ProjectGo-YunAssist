<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Todo extends Model
{
    protected $fillable = ['project_id', 'title', 'status', 'priority', 'completed_at'];

    protected $casts = ['completed_at' => 'datetime'];

    protected $with = ['tags'];

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workLog(): HasOne
    {
        return $this->hasOne(WorkLog::class);
    }
}
