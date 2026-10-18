<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
    ];

    /**
     * カラムの型キャスト定義
     */
    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     * この書籍を登録したユーザーを取得する
     *
     * @return BelongsTo 書籍を登録したユーザーとのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * この書籍に紐づくジャンルを取得する
     *
     * @return BelongsToMany 書籍とジャンルの多対多リレーション
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->withTimestamps();
    }

    /**
     * この書籍に紐づくレビューを取得する
     *
     * @return HasMany 書籍とレビューの1対多リレーション
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * この書籍をお気に入り登録しているユーザーを取得する
     *
     * @return BelongsToMany 書籍とお気に入り登録ユーザーの多対多リレーション
     */
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * この書籍に紐づく読書計画を取得する
     *
     * @return HasMany 書籍と読書計画の1対多リレーション
     */
    public function readingPlans(): HasMany
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
