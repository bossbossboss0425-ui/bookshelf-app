<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * ログインユーザーのお気に入り書籍一覧を表示する
     *
     * @return View お気に入り書籍一覧画面
     */
    public function index(): View
    {
        $user = Auth::user();

        // ログインユーザーがお気に入り登録した書籍をペジネーション取得
        $books = $user->favoriteBooks()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->latest('favorites.created_at') // お気に入りに登録した日時が新しい順
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * 書籍のお気に入り追加・解除を切り替える
     *
     * @param  Book  $book  お気に入り登録・解除の対象書籍
     * @return RedirectResponse 直前の画面へのリダイレクト
     */
    public function toggle(Book $book): RedirectResponse
    {
        $user = Auth::user();

        // 既にお気に入り登録されていれば解除、されていなければ追加
        $user->favoriteBooks()->toggle($book->id);

        return back()->with('success', 'お気に入りを更新しました。');
    }
}
