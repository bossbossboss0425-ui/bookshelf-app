<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_at',
        'description',
        'image_url',
    ];

    // 書籍を登録したユーザー
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 書籍に紐づくジャンル（多対多）
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->withTimestamps();
    }

    // 書籍に紐づくレビュー
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // この書籍をお気に入り登録しているユーザー（多対多）
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}