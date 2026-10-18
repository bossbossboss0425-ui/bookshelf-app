<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する
     *
     * @return View ジャンル一覧画面
     */
    public function index(): View
    {
        // 関連する書籍数をカウントして一覧取得
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル別書籍一覧を表示する
     *
     * @param  Genre  $genre  表示対象のジャンル
     * @return View ジャンル別書籍一覧画面
     */
    public function show(Genre $genre): View
    {
        // このジャンルに紐づく書籍をペジネーション（10件/ページ）で取得
        $books = $genre->books()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル登録フォームを表示する
     *
     * @return View ジャンル登録画面
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * 新規ジャンルを保存する
     *
     * @param  Request  $request  ジャンルの登録データ
     * @return RedirectResponse ジャンル一覧画面へのリダイレクト
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:genres,name'],
        ]);

        Genre::create($validated);

        return redirect()->route('genres.index')->with('success', 'ジャンルを作成しました。');
    }

    /**
     * ジャンル編集フォームを表示する
     *
     * @param  Genre  $genre  編集対象のジャンル
     * @return View ジャンル編集画面
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンル情報を更新する
     *
     * @param  Request  $request  ジャンルの更新データ
     * @param  Genre  $genre  更新対象のジャンル
     * @return RedirectResponse ジャンル一覧画面へのリダイレクト
     */
    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:genres,name,'.$genre->id],
        ]);

        $genre->update($validated);

        return redirect()->route('genres.index')->with('success', 'ジャンル名を更新しました。');
    }

    /**
     * ジャンルを削除する
     *
     * @param  Genre  $genre  削除対象のジャンル
     * @return RedirectResponse ジャンル一覧画面または直前の画面へのリダイレクト
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        // 書籍に紐づいているかチェック
        if ($genre->books()->exists()) {
            return back()->with('error', '書籍に紐づいているジャンルは削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }
}
