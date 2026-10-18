<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * 通知一覧を表示する
     *
     * @return View 通知一覧画面
     */
    public function index(): View
    {
        $notifications = Auth::user()->notifications()->latest()->get();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 指定された通知を既読にする
     *
     * @param  string  $id  既読にする通知のID
     * @return RedirectResponse 通知一覧画面へのリダイレクト
     */
    public function read(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }
}
