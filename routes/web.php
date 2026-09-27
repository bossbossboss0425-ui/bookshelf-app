<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// トップページ（/）および書籍一覧 (/books) は未ログインでも閲覧可能
Route::get('/', [BookController::class, 'index'])->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// ランキング画面 (PG11)
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// ジャンル一覧 (PG08)
Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

/*
|--------------------------------------------------------------------------
| 認証必須ルート (要ログイン)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // 通知一覧の仮ルート（書籍一覧へリダイレクト）
    Route::get('/notifications', function () {
        return redirect()->route('books.index');
    })->name('notifications.index');
    // 読書計画の仮ルート（トップページや別の画面へリダイレクト、または空ビューを返す）
    Route::get('/reading-plans', function () {
        return redirect()->route('books.index');
    })->name('reading-plans.index');
    // マイ読書レポート
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    // ★ /books/create を /books/{book} よりも前に定義
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    // ★ ISBN検索ルート（/books/{book} より前に配置することで誤判定を防止）
    Route::get('/books/isbn/{isbn}', [BookController::class, 'fetchByIsbn'])->name('books.isbn');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::post('/reviews/{review}/like', [BookController::class, 'likeReview'])
        ->name('reviews.like');

    // ★ /genres/create を /genres/{genre} よりも前に定義 (PG10)
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    Route::put('/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');

    // レビューの投稿・編集・更新・削除 (PG02, PG06)
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // お気に入り一覧・切り替え (PG05)
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

/*
|--------------------------------------------------------------------------
| パラメータを含む詳細系ルート（固定パスより後ろに定義）
|--------------------------------------------------------------------------
*/
// ジャンル詳細 (PG09)
Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');

// 書籍詳細 (PG02) - 未ログイン閲覧可
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
