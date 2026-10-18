<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingPlan\StoreReadingPlanRequest;
use App\Http\Requests\ReadingPlan\UpdateReadingPlanRequest;
use App\Models\ReadingPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧を表示し、ステータスで絞り込む
     *
     * @param  Request  $request  絞り込み条件を含むリクエスト
     * @return View 読書計画一覧画面
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->query('status');

        $query = Auth::user()->readingPlans()->with('book');

        if ($currentStatus) {
            $query->where('status', $currentStatus);
        }

        $readingPlans = $query->latest()->paginate(10);

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 読書計画の新規作成画面を表示する
     *
     * @return View 読書計画の新規作成画面
     */
    public function create(): View
    {
        // ユーザーが登録した書籍一覧（ドロップダウン用）
        $books = Auth::user()->books()->select('id', 'title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 新しい読書計画を登録する
     *
     * @param  StoreReadingPlanRequest  $request  読書計画の登録データ
     * @return RedirectResponse 読書計画一覧画面へのリダイレクト
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        Auth::user()->readingPlans()->create([
            'book_id' => $request->validated('book_id'),
            'target_date' => $request->validated('target_date'),
            'status' => 'in_progress',
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を作成しました。');
    }

    /**
     * 読書計画の編集画面を表示する
     *
     * @param  ReadingPlan  $readingPlan  編集対象の読書計画
     * @return View 読書計画の編集画面
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 読書計画の目標日を更新する
     *
     * @param  UpdateReadingPlanRequest  $request  読書計画の更新データ
     * @param  ReadingPlan  $readingPlan  更新対象の読書計画
     * @return RedirectResponse 読書計画一覧画面へのリダイレクト
     */
    public function update(
        UpdateReadingPlanRequest $request,
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'target_date' => $request->validated('target_date'),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を読了にし、ステータスと読了日時を更新する
     *
     * @param  ReadingPlan  $readingPlan  読了にする読書計画
     * @return RedirectResponse 読書計画一覧画面へのリダイレクト
     */
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '計画を読了に更新しました！');
    }

    /**
     * 読書計画を削除する
     *
     * @param  ReadingPlan  $readingPlan  削除対象の読書計画
     * @return RedirectResponse 読書計画一覧画面へのリダイレクト
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }
}
