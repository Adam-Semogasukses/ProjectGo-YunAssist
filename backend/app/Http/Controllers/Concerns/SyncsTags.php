<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait SyncsTags
{
    /** Jika request memuat `tags` (array nama), ganti tag model dengan daftar itu. */
    protected function syncTags(Model $model, Request $request): void
    {
        if (! $request->has('tags')) {
            return;
        }
        $names = $request->validate([
            'tags' => 'array|max:20',
            'tags.*' => 'string|max:50',
        ])['tags'];
        $model->tags()->sync(Tag::idsFor($names));
        $model->unsetRelation('tags');
    }
}
