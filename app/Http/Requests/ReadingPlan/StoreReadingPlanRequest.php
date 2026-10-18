<?php

namespace App\Http\Requests\ReadingPlan;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
{
    /**
     * 読書計画登録リクエストの実行を許可する
     *
     * @return bool リクエストを許可する場合はtrue
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 読書計画登録時のバリデーションルールを定義する
     *
     * 書籍の存在と、目標日が本日以降であることを検証する。
     *
     * @return array<string, mixed> バリデーションルールの配列
     */
    public function rules(): array
    {
        return [
            'book_id' => ['required', 'exists:books,id'],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * 読書計画登録時のバリデーションエラーメッセージを定義する
     *
     * @return array<string, string> エラーメッセージの配列
     */
    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '選択された書籍が存在しません。',
            'target_date.required' => '期日を入力してください。',
            'target_date.date' => '正しい日付形式で入力してください。',
            'target_date.after_or_equal' => '期日は本日以降の日付を指定してください。',
        ];
    }
}
