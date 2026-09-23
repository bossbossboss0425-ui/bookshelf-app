<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\StoreBookRequest;
use App\Http\Requests\Api\v1\UpdateBookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookApiController extends Controller
{
    /**
     * AP01: 書籍一覧取得 API
     */
    public function index(Request $request)
    {
        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // キーワード検索（タイトル・著者）
        if ($keyword = $request->input('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンル絞り込み
        if ($genreId = $request->input('genre_id')) {
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        $books = $query->latest()->paginate($request->input('per_page', 10));

        return BookResource::collection($books);
    }

    /**
     * AP02: 書籍詳細取得 API
     */
    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return new BookDetailResource($book);
    }

    /**
     * AP03: 書籍登録 API
     */
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated, $request) {
            // 認証なしのため、テストユーザーまたはログイン中のIDを設定（環境に合わせて調整）
            $validated['user_id'] = auth()->id() ?? 1;

            $newBook = Book::create($validated);

            if (! empty($request->genre_ids)) {
                $newBook->genres()->sync($request->genre_ids);
            }

            return $newBook;
        });

        return (new BookDetailResource($book->load(['genres', 'reviews'])))
            ->response()
            ->setStatusCode(201); // 201 Created
    }

    /**
     * AP04: 書籍更新 API
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($book, $validated, $request) {
            $book->update($validated);

            if ($request->has('genre_ids')) {
                $book->genres()->sync($request->genre_ids);
            }
        });

        return new BookDetailResource($book->load(['genres', 'reviews.user']));
    }

    /**
     * AP05: 書籍削除 API
     */
    public function destroy(Book $book)
    {
        DB::transaction(function () use ($book) {
            // 関連データの適切な削除（多対多中間テーブルの解消）
            $book->genres()->detach();
            $book->favoritedByUsers()->detach();
            $book->reviews()->delete(); // レビュー自体の削除

            $book->delete();
        });

        return response()->json([
            'message' => '書籍情報を削除しました。',
        ], 200);
    }
}
