<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * 書籍の更新権限を判定する
     *
     * ログインユーザーが対象書籍の作成者本人であるか確認する。
     *
     * @param  User  $user  認可を確認するユーザー
     * @param  Book  $book  更新対象の書籍
     * @return bool 更新を許可する場合はtrue
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * 書籍の削除権限を判定する
     *
     * ログインユーザーが対象書籍の作成者本人であるか確認する。
     *
     * @param  User  $user  認可を確認するユーザー
     * @param  Book  $book  削除対象の書籍
     * @return bool 削除を許可する場合はtrue
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }
}
