<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    protected $fillable = ['project_id', 'event_date', 'title', 'event_type'];

    protected $casts = ['event_date' => 'date:Y-m-d'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
