<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            // 呼び出し側で指定されなかった場合のデフォルト動作
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'rating' => $this->faker->numberBetween(3, 5), // 要件：3〜5の範囲
            'comment' => $this->faker->realText(80),      // 日本語のダミーコメント（要件に応じて）
        ];
    }
}
