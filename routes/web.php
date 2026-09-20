<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
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
    // ★ /books/create を /books/{book} よりも前に定義
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
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
