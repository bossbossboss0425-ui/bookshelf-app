<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->count() < 2 || $books->count() < 6) {
            return; // 必要なユーザー数・書籍数が不足している場合は処理しない
        }

        // 山田太郎 (最初のユーザー), 鈴木花子 (2番目のユーザー)
        $yamada = $users->first();
        $suzuki = $users->skip(1)->first();

        $today = Carbon::today();

        $plans = [
            // 山田太郎シナリオ (ID 1〜5 想定)
            [
                'user_id' => $yamada->id,
                'book_id' => $books[0]->id,
                'target_date' => $today->copy()->addDays(3),
                'status' => 'in_progress',
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books[1]->id,
                'target_date' => $today->copy(),
                'status' => 'in_progress',
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books[2]->id,
                'target_date' => $today->copy()->subDays(3),
                'status' => 'in_progress',
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books[3]->id,
                'target_date' => $today->copy()->addDays(7),
                'status' => 'in_progress',
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books[4]->id,
                'target_date' => $today->copy()->subDays(10),
                'status' => 'completed',
                'completed_at' => $today->copy()->subDays(5),
            ],

            // 鈴木花子シナリオ (ID 6 想定 / 認可テスト用)
            [
                'user_id' => $suzuki->id,
                'book_id' => $books[5]->id,
                'target_date' => $today->copy()->addDays(5),
                'status' => 'in_progress',
                'completed_at' => null,
            ],
        ];

        foreach ($plans as $plan) {
            ReadingPlan::create($plan);
        }
    }
}