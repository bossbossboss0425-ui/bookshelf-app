<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        foreach ($reviews as $review) {
            // 自分のレビュー以外から0〜3人のユーザーを選択
            $otherUsers = $users->where('id', '!=', $review->user_id);
            $likeCount = rand(0, min(3, $otherUsers->count()));

            if ($likeCount > 0) {
                $likerIds = $otherUsers->random($likeCount)->pluck('id');
                $review->likedByUsers()->syncWithoutDetaching($likerIds);
            }
        }
    }
}