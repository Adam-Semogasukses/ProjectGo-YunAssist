<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyNote extends Model
{
    protected $fillable = ['date', 'focus_note'];

    protected $casts = ['date' => 'date:Y-m-d'];
}
