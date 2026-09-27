<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * マイ読書レポート画面を表示
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // ログインユーザーの全レビューを取得（書籍とジャンルもEager Loading）
        $reviews = Review::with('book.genres')
            ->where('user_id', $user->id)
            ->get();

        // 1. 基本サマリー
        $totalReviews = $reviews->count();
        $booksRead = $reviews->pluck('book_id')->unique()->count();
        $averageRating = $totalReviews > 0 ? $reviews->avg('rating') : 0;

        // 2. 評価分布（1〜5の各件数をカウント。キーは 0〜4 のインデックスに対応）
        $ratingCounts = $reviews->groupBy('rating');
        $ratingDistribution = collect([1, 2, 3, 4, 5])->map(function ($rating) use ($ratingCounts) {
            return isset($ratingCounts[$rating]) ? $ratingCounts[$rating]->count() : 0;
        });

        // 3. 高評価書籍 TOP5（評価4以上、高い順に最大5件）
        $topRatedBooks = $reviews
            ->filter(fn ($review) => $review->rating >= 4)
            ->sortByDesc('rating')
            ->take(5)
            ->map(function ($review) {
                return [
                    'id' => $review->book_id,
                    'title' => $review->book->title ?? 'タイトル不明',
                    'author' => $review->book->author ?? '著者不明',
                    'rating' => $review->rating,
                ];
            })
            ->values()
            ->all();

        // 4. ジャンル別評価傾向 TOP5（平均評価順、同点の場合は件数順で最大5件）
        // レビューした書籍に紐づくジャンル情報を集計
        $genreStats = [];
        foreach ($reviews as $review) {
            if ($review->book && $review->book->genres) {
                foreach ($review->book->genres as $genre) {
                    if (! isset($genreStats[$genre->id])) {
                        $genreStats[$genre->id] = [
                            'id' => $genre->id,
                            'name' => $genre->name,
                            'ratings' => [],
                        ];
                    }
                    $genreStats[$genre->id]['ratings'][] = $review->rating;
                }
            }
        }

        $genreRatings = collect($genreStats)
            ->map(function ($data) {
                $count = count($data['ratings']);
                $avg = $count > 0 ? array_sum($data['ratings']) / $count : 0;

                return [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'count' => $count,
                    'average_rating' => $avg,
                ];
            })
            ->sortByDesc(fn ($item) => [$item['average_rating'], $item['count']])
            ->take(5)
            ->values()
            ->all();

        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
