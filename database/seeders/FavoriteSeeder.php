<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        // 各ユーザーごとに3〜5冊のお気に入りを設定
        $favoritesMap = [
            0 => [0, 1, 3, 7, 9],    // ユーザー0: 5冊
            1 => [1, 3, 7, 8],       // ユーザー1: 4冊
            2 => [0, 2, 4, 9],       // ユーザー2: 4冊
            3 => [1, 2, 5, 8, 10],   // ユーザー3: 5冊
            4 => [2, 5, 6],          // ユーザー4: 3冊
        ];

        foreach ($favoritesMap as $userIndex => $bookIndexes) {
            if (isset($users[$userIndex])) {
                $bookIds = $books->only($bookIndexes)->pluck('id');
                $users[$userIndex]->favoriteBooks()->syncWithoutDetaching($bookIds);
            }
        }
    }
}
