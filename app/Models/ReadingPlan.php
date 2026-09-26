<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPlan extends Model
{
    use HasFactory;

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'status',
        'completed_at',
    ];

    /**
     * 日付型にキャストする属性
     */
    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * 計画を作成したユーザー（リレーション）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 対象の書籍（リレーション）
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}