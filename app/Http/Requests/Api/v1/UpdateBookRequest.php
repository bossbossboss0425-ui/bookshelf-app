<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    /**
     * API書籍更新リクエストの実行を許可する
     *
     * 書籍の更新権限はコントローラー側で確認する。
     *
     * @return bool リクエストを許可する場合はtrue
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * API書籍更新時のバリデーションルールを定義する
     *
     * ISBNの重複チェックでは、更新対象の書籍自身を除外する。
     *
     * @return array<string, mixed> バリデーションルールの配列
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => [
                'required',
                'string',
                'size:13',
                Rule::unique('books', 'isbn')->ignore($this->route('book')), // 自身のISBNを除外
            ],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['exists:genres,id'],
        ];
    }
}
