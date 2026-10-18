<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * API書籍登録リクエストの実行を許可する
     *
     * 認証の確認はルートのミドルウェアで行う。
     *
     * @return bool リクエストを許可する場合はtrue
     */
    public function authorize(): bool
    {
        return true; // 認証なしで許可
    }

    /**
     * API書籍登録時のバリデーションルールを定義する
     *
     * @return array<string, mixed> バリデーションルールの配列
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'size:13', 'unique:books,isbn'],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['exists:genres,id'],
        ];
    }
}
