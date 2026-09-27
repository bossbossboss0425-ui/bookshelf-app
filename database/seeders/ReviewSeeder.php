<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        // 評価別日本語テンプレート5段階
        $templates = [
            5 => ['素晴らしい本でした！', '人生が変わりました。', '何度も読み返しています。'],
            4 => ['とても参考になりました。', '読みやすくておすすめです。', '期待通りの内容でした。'],
            3 => ['普通でした。', '可もなく不可もなく。', '期待したほどではなかった。'],
            2 => ['少し期待外れでした。', '内容が薄い印象。', 'もう少し深掘りしてほしかった。'],
            1 => ['残念ながら合いませんでした。', '期待と違いました。'],
        ];

        // 各書籍（11冊）に2〜4件のレビューを配分
        foreach ($books as $book) {
            $reviewCount = rand(2, 4);

            // 投稿者をランダム選出（同一書籍への重複投稿を防止）
            $reviewers = $users->random(min($reviewCount, $users->count()));

            foreach ($reviewers as $reviewer) {
                // 1〜5の全範囲で評価をランダム設定
                $rating = rand(1, 5);

                // 評価に応じたコメントを選出
                $commentList = $templates[$rating];
                $comment = $commentList[array_rand($commentList)];

                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $reviewer->id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);
            }
        }
    }
}