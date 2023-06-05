<?php

namespace App\Http\Requests\UserInfo;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateInfoRequest extends FormRequest
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
            // 'name' => 'required|max:255',
            'full_name' => 'required|max:255',
            'email' => 'required|email:rfc|max:150',
            'sex' => 'required',
            'birth_day' => 'required',
            'phone' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'email is required',
            'email.email' => 'The email type is invalid',
            'email.max' => 'email must be great than 150',
            'name.required' => 'name is required',
            'name.max' => 'name must be great than 255',
            'full_name.required' => 'full_name is required',
            'full_name.max' => 'full_name must be great than 255',
            'sex.required' => 'sex is required',
            'birth_day.required' => 'birth day is required',
            'phone.required' => 'phone is required',
            'phone.numeric' => 'phone is numeric',
        ];
    }
}
