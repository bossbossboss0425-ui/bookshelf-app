<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    /**
     * 書籍ランキング画面を表示 (PG11)
     */
    public function index()
    {
        // レビューの平均評価が高い順に取得（同点の場合はレビュー数が多い順）
        $rankedBooks = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
