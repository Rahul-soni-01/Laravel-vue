<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function rules()
    {
        return [
            'email' => 'required|email:rfc',
            'token' => 'required',
            'password' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールが必要です',
            'email.email' => 'メールフォーマットを入力してください',
            'token.required' => 'トークンの検証が必要です',
            'password.required' => 'パスワードが必要です',
        ];
    }
}
