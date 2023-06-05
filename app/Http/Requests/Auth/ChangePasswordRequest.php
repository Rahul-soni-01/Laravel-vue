<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'new_password' => 'required',
            'old_password' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'new_password' => 'new_passwordが必要です',
            'old_password' => 'old_passwordが必要です'
        ];
    }
}
