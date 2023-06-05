<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTempRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => 'required|email:rfc'
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'このメールアドレスは既に登録されました。',
            'email.required' => 'メールが必要です',
            'email.email' => 'メールフォーマットを入力してください',
        ];
    }
}
