<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'slug', 'color'];

    protected $hidden = ['pivot'];

    private const PALETTE = ['#6366f1', '#10b981', '#f59e0b', '#06b6d4', '#ec4899', '#8b5cf6', '#ef4444', '#84cc16'];

    /** Cari-atau-buat tag dari daftar nama; kembalikan daftar id. */
    public static function idsFor(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $name = ltrim(trim($name), '#');
            $slug = Str::slug($name);
            if ($slug === '') {
                continue;
            }
            $tag = static::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'color' => self::PALETTE[crc32($slug) % count(self::PALETTE)],
            ]);
            $ids[] = $tag->id;
        }
        return array_values(array_unique($ids));
    }
}
