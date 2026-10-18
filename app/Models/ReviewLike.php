<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLike extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'review_id'];

    /**
     * 「いいね」をしたユーザーを取得する
     *
     * @return BelongsTo ユーザーとのリレーション
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 「いいね」されたレビューを取得する
     *
     * @return BelongsTo レビューとのリレーション
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
