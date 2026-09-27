<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示 (10件/ページ)
     */
    public function index(Request $request)
    {
        // 検索フォームのジャンル選択肢用
        $genres = Genre::all();

        // ベースとなるクエリの作成（Eager Loading と 平均評価の取得）
        $query = Book::with('genres')
            ->withAvg('reviews', 'rating');

        // 1. キーワード検索（タイトル または 著者名）
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // 2. ジャンル絞り込み（多対多リレーション）
        if ($request->filled('genre')) {
            $genreId = $request->input('genre');
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        // 3. ソート順の処理
        $sort = $request->input('sort', 'latest');

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'rating':
                // 評価が高い順（未評価の書籍は最後に配置）
                $query->orderByRaw('reviews_avg_rating IS NULL ASC')
                    ->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 4. ページネーション (10件/ページ) & 検索クエリ文字列の保持
        $books = $query->paginate(10)->appends($request->query());

        // Blade 側で必要とされる $books と $genres の両方を渡す
        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録フォームを表示
     */
    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 新規書籍を保存
     */
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $book = Book::create($validated);

        if (isset($validated['genres'])) {
            $book->genres()->sync($validated['genres']);
        }

        return redirect()->route('books.index')->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍詳細を表示
     */
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ])->loadAvg('reviews', 'rating');

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集フォームを表示
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍情報を更新
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();
        $book->update($validated);

        if (isset($validated['genres'])) {
            $book->genres()->sync($validated['genres']);
        }

        return redirect()->route('books.show', $book)->with('success', '書籍情報を更新しました。');
    }

    /**
     * 書籍を削除（関連データも自動/明示的に削除）
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->genres()->detach(); // 中間テーブル解除
        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }

    /**
     * レビューの「いいね」をトグル（追加/解除）する
     */
    public function likeReview(Review $review)
    {
        $review->likedByUsers()->toggle(auth()->id());

        return back();
    }

    /**
     * ISBNからGoogle Books APIを利用して書籍情報を取得する
     */
    public function fetchByIsbn(string $isbn)
    {
        // ハイフンを除去
        $isbn = str_replace('-', '', $isbn);

        // ISBNの形式チェック（半角数字13桁）
        if (! preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁の半角数字で入力してください。',
            ], 400);
        }

        try {
            // リクエストパラメータの組み立て
            $params = [
                'q' => 'isbn:'.$isbn,
            ];

            // .env に APIキーが設定されている場合はパラメータに追加
            if (env('GOOGLE_BOOKS_API_KEY')) {
                $params['key'] = env('GOOGLE_BOOKS_API_KEY');
            }

            // Google Books API へリクエスト
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ])
                ->get('https://www.googleapis.com/books/v1/volumes', $params);

            if ($response->failed()) {
                \Log::error('Google Books API Error: '.$response->status().' '.$response->body());

                return response()->json([
                    'error' => '書籍情報の取得に失敗しました。',
                ], 500);
            }

            $data = $response->json();

            // 該当する書籍が見つからない場合
            if (empty($data['items'])) {
                return response()->json([
                    'error' => '該当する書籍が見つかりませんでした。',
                ], 404);
            }

            $volumeInfo = $data['items'][0]['volumeInfo'] ?? [];

            // 出版日のパース（Blade側の JS: new Date() が確実に処理できる ISO 8601 形式にする）
            $rawDate = $volumeInfo['publishedDate'] ?? '';
            $publishedDate = '';

            if ($rawDate) {
                if (strlen($rawDate) === 4) {
                    $rawDate .= '-01-01';
                } elseif (strlen($rawDate) === 7) {
                    $rawDate .= '-01';
                }

                $publishedDate = date('Y-m-d\TH:i:s\Z', strtotime($rawDate));
            }

            return response()->json([
                'title' => $volumeInfo['title'] ?? '',
                'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '',
                'published_date' => $publishedDate,
                'description' => $volumeInfo['description'] ?? '',
                'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? ($volumeInfo['imageLinks']['smallThumbnail'] ?? ''),
            ]);

        } catch (\Exception $e) {
            \Log::error('ISBN Fetch Exception: '.$e->getMessage());

            return response()->json([
                'error' => 'サーバーエラーが発生しました。',
            ], 500);
        }
    }
}
