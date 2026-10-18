<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * 書籍にレビューを投稿する
     *
     * 評価とコメントをバリデーションし、ログインユーザーのレビューを保存する。
     *
     * @param  Request  $request  レビューの投稿データ
     * @param  Book  $book  レビュー対象の書籍
     * @return RedirectResponse 書籍詳細画面へのリダイレクト
     */
    public function store(Request $request, Book $book): RedirectResponse
    {
        // 1. バリデーション
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        // 2. ログインユーザーIDを設定してレビュー保存
        $book->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集フォームを表示する
     *
     * ポリシーでレビューの編集権限を確認する。
     *
     * @param  Review  $review  編集対象のレビュー
     * @return View レビュー編集画面
     */
    public function edit(Review $review): View
    {
        // 本人のレビューかチェック
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する
     *
     * 編集権限を確認し、評価とコメントを更新する。
     *
     * @param  Request  $request  レビューの更新データ
     * @param  Review  $review  更新対象のレビュー
     * @return RedirectResponse 書籍詳細画面へのリダイレクト
     */
    public function update(Request $request, Review $review): RedirectResponse
    {
        // 本人のレビューかチェック
        $this->authorize('update', $review);

        // バリデーション
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $review->update($validated);

        return redirect()
            ->route('books.show', $review->book_id)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューを削除する
     *
     * 削除権限を確認してレビューを削除し、対象書籍の詳細画面へ戻る。
     *
     * @param  Review  $review  削除対象のレビュー
     * @return RedirectResponse 書籍詳細画面へのリダイレクト
     */
    public function destroy(Review $review): RedirectResponse
    {
        // 本人のレビューかチェック
        $this->authorize('delete', $review);

        $bookId = $review->book_id;
        $review->delete();

        return redirect()
            ->route('books.show', $bookId)
            ->with('success', 'レビューを削除しました。');
    }
}
