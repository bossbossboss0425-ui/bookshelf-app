<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * レビューを投稿
     */
    public function store(Request $request, Book $book)
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

        return back()->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集フォーム表示
     */
    public function edit(Review $review)
    {
        // 本人のレビューかチェック
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新
     */
    public function update(Request $request, Review $review)
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
     * レビューを削除
     */
    public function destroy(Review $review)
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
