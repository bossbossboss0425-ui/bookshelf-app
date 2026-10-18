<?php

namespace App\Http\Requests\ReadingPlan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingPlanRequest extends FormRequest
{
    /**
     * 読書計画更新リクエストの実行を許可する
     *
     * @return bool リクエストを許可する場合はtrue
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 読書計画更新時のバリデーションルールを定義する
     *
     * 目標日が入力され、有効な日付形式であることを検証する。
     *
     * @return array<string, mixed> バリデーションルールの配列
     */
    public function rules(): array
    {
        return [
            'target_date' => ['required', 'date'],
        ];
    }

    /**
     * 読書計画更新時のバリデーションエラーメッセージを定義する
     *
     * @return array<string, string> エラーメッセージの配列
     */
    public function messages(): array
    {
        return [
            'target_date.required' => '期日を入力してください。',
            'target_date.date' => '正しい日付形式で入力してください。',
        ];
    }
}
