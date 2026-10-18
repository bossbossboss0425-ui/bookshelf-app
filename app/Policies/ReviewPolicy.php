<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * レビューの更新権限を判定する
     *
     * ログインユーザーが対象レビューの投稿者本人であるか確認する。
     *
     * @param  User  $user  認可を確認するユーザー
     * @param  Review  $review  更新対象のレビュー
     * @return bool 更新を許可する場合はtrue
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * レビューの削除権限を判定する
     *
     * ログインユーザーが対象レビューの投稿者本人であるか確認する。
     *
     * @param  User  $user  認可を確認するユーザー
     * @param  Review  $review  削除対象のレビュー
     * @return bool 削除を許可する場合はtrue
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}
