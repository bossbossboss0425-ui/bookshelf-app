<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        // リアルなコメントリスト
        $comments = [
            '人間社会風刺の鋭さとユーモアが最高です。何度も読み返したくなります。',
            '職場での人間関係に悩んだ時に読んで救われました。一生モノのバイブルです。',
            '全エンジニア必読の一冊。変数名のつけ方から設計まで意識が変わりました。',
            '主体的に生きるためのマインドセットを学びました。定期的に読み直します。',
            '正義感あふれる主人公の真っ直ぐな生き方が爽快で好きです。',
            '「虚構」を信じる力が人類を発展させたという視点に衝撃を受けました。',
            'プロとしてのコードの書き方を徹底的に叩き込んでくれる本です。',
            '他人の課題と自分の課題を分離するという考え方にとても救われました。',
            '夢を追うことの厳しさと美しさが綺麗に描かれています。',
            '自分がどれほど偏見で世界を見ていたかに気付かされました。全員読むべき。',
        ];

        // 11冊の書籍それぞれに2〜4件のレビューを配分（合計約32件）
        foreach ($books as $book) {
            $reviewCount = rand(2, 4); // 各書籍に2〜4件

            Review::factory()
                ->count($reviewCount)
                ->state(new Sequence(
                    fn () => [
                        'user_id' => $users->random()->id,
                        'comment' => $comments[array_rand($comments)],
                    ]
                ))
                ->create([
                    'book_id' => $book->id,
                ]);
        }
    }
}
