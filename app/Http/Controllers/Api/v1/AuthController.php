<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * メールアドレスとパスワードを検証し、API認証トークンを発行する
     *
     * 認証に失敗した場合は401を返し、成功した場合はBearerトークンを返す。
     *
     * @param  Request  $request  ログイン情報を含むリクエスト
     * @return JsonResponse 認証結果またはアクセストークンを含むJSONレスポンス
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // 認証失敗時：401 Unauthorized を返却
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードが正しくありません。',
            ], 401);
        }

        // Bearer トークンを発行
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * 現在のAPI認証トークンを破棄してログアウトする
     *
     * @param  Request  $request  認証済みユーザーのリクエスト
     * @return JsonResponse ログアウト結果を含むJSONレスポンス
     */
    public function logout(Request $request): JsonResponse
    {
        // 現在のリクエストで使用されたトークンを削除
        /** @var PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'message' => 'ログアウトしました。',
        ], 200);
    }
}
