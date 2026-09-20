<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示 (PG08)
     */
    public function index()
    {
        // 関連する書籍数をカウントして一覧取得
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル別書籍一覧を表示 (PG09)
     */
    public function show(Genre $genre)
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
     * ジャンル登録フォームを表示 (PG10)
     */
    public function create()
    {
        return view('genres.create');
    }

    /**
     * 新規ジャンルを保存 (PG10)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:genres,name'],
        ]);

        Genre::create($validated);

        return redirect()->route('genres.index')->with('success', 'ジャンルを作成しました。');
    }

    /**
     * ジャンル編集フォームを表示 (PG10)
     */
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンル情報を更新 (PG10)
     */
    public function update(Request $request, Genre $genre)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:genres,name,'.$genre->id],
        ]);

        $genre->update($validated);

        return redirect()->route('genres.index')->with('success', 'ジャンル名を更新しました。');
    }

    /**
     * ジャンルを削除 (PG10)
     */
    public function destroy(Genre $genre)
    {
        // 書籍に紐づいているかチェック
        if ($genre->books()->exists()) {
            return back()->with('error', '書籍に紐づいているジャンルは削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }
}
