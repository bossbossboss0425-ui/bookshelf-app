<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPolicy
{
    /**
     * 読書計画の表示権限を判定する
     *
     * ログインユーザーが対象の読書計画の所有者本人であるか確認する。
     *
     * @param  User  $user  認可を確認するユーザー
     * @param  ReadingPlan  $readingPlan  表示対象の読書計画
     * @return bool 表示を許可する場合はtrue
     */
    public function view(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    /**
     * 読書計画の更新権限を判定する
     *
     * ログインユーザーが対象の読書計画の所有者本人であるか確認する。
     *
     * @param  User  $user  認可を確認するユーザー
     * @param  ReadingPlan  $readingPlan  更新対象の読書計画
     * @return bool 更新を許可する場合はtrue
     */
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    /**
     * 読書計画の削除権限を判定する
     *
     * ログインユーザーが対象の読書計画の所有者本人であるか確認する。
     *
     * @param  User  $user  認可を確認するユーザー
     * @param  ReadingPlan  $readingPlan  削除対象の読書計画
     * @return bool 削除を許可する場合はtrue
     */
    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }
}
