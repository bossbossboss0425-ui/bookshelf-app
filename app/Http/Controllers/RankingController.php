<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;

class RankingController extends Controller
{
    /**
     * 書籍ランキング画面を表示する
     *
     * @return View 書籍ランキング画面
     */
    public function index(): View
    {
        // レビューの平均評価が高い順に取得（同点の場合はレビュー数が多い順）
        $rankedBooks = Book::has('reviews')
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
