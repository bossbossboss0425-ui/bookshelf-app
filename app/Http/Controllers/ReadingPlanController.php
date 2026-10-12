<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReadingPlan\StoreReadingPlanRequest;
use App\Http\Requests\ReadingPlan\UpdateReadingPlanRequest;
use App\Models\ReadingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧・絞り込み表示
     */
    public function index(Request $request)
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
     * 新規作成画面表示
     */
    public function create()
    {
        // ユーザーが登録した書籍一覧（ドロップダウン用）
        $books = Auth::user()->books()->select('id', 'title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 新規登録処理
     */
    public function store(StoreReadingPlanRequest $request)
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
     * 編集画面表示
     */
    public function edit(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 更新処理
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'target_date' => $request->validated('target_date'),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読了（ステータス変更）処理
     */
    public function complete(ReadingPlan $readingPlan)
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
     * 削除処理
     */
    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }
}
