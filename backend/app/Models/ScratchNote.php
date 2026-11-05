<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ScratchNote extends Model
{
    protected $fillable = ['content', 'is_archived'];

    protected $casts = ['is_archived' => 'boolean'];

    protected $with = ['tags'];

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
