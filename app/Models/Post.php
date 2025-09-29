<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    // ★ これを追加
    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
        protected $fillable = [
        'user_id','title','slug','body',
        'is_published','published_at',
        'thumbnail_path',   // ← 追加
    ];

    // サムネイルの公開URL（無ければプレースホルダ）
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail_path) {
            return asset('storage/'.$this->thumbnail_path);
        }
        return asset('images/placeholder.png'); // 任意。無ければ適宜用意
    }
}
