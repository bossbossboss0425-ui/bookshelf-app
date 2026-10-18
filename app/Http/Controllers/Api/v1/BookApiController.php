<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\StoreBookRequest;
use App\Http\Requests\Api\v1\UpdateBookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BookApiController extends Controller
{
    /**
     * AP01: 書籍一覧取得 API
     *
     * キーワード検索やジャンル絞り込みを行い、ページネーション付きで書籍一覧を返す。
     *
     * @param  Request  $request  検索・絞り込み条件を含むリクエスト
     * @return AnonymousResourceCollection 書籍一覧のリソースコレクション
     */
    public function index(Request $request): AnonymousResourceCollection
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

        $books = $query->latest()->paginate($request->input('per_page', 20));

        return BookResource::collection($books);
    }

    /**
     * AP02: 書籍詳細取得 API
     *
     * 書籍に関連するジャンルとレビュー・投稿者を読み込み、詳細情報を返す。
     *
     * @param  Book  $book  詳細を取得する書籍
     * @return BookDetailResource 書籍詳細リソース
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load(['genres', 'reviews.user']);

        return new BookDetailResource($book);
    }

    /**
     * AP03: 書籍登録 API
     *
     * 書籍と関連ジャンルをトランザクション内で登録する。
     *
     * @param  StoreBookRequest  $request  書籍の登録データ
     * @return JsonResponse 登録した書籍情報を含む201レスポンス
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated, $request) {
            // Sanctum認証されたログインユーザーのIDを設定
            $validated['user_id'] = auth()->id();

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
     *
     * 書籍の作成者本人であることを確認し、書籍情報と関連ジャンルを更新する。
     *
     * @param  UpdateBookRequest  $request  書籍の更新データ
     * @param  Book  $book  更新対象の書籍
     * @return JsonResponse|BookDetailResource 更新結果または権限エラー
     */
    public function update(
        UpdateBookRequest $request,
        Book $book
    ): JsonResponse|BookDetailResource {
        // 認可チェック: 本の作成者本人でなければ 403 Forbidden
        if ($book->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'この書籍情報を更新する権限がありません。',
            ], 403);
        }

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
     *
     * 書籍の作成者本人であることを確認し、関連データと書籍を削除する。
     *
     * @param  Book  $book  削除対象の書籍
     * @return JsonResponse 削除結果または権限エラーを含むJSONレスポンス
     */
    public function destroy(Book $book): JsonResponse
    {
        // 認可チェック: 本の作成者本人でなければ 403 Forbidden
        if ($book->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'この書籍情報を削除する権限がありません。',
            ], 403);
        }

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
