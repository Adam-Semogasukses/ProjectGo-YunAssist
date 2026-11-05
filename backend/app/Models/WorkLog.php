<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    protected $fillable = [
        'todo_id', 'date', 'start_time', 'end_time',
        'activity', 'type', 'notes', 'is_manually_edited',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_manually_edited' => 'boolean',
    ];
}
