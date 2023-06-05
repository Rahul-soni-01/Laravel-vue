<?php

namespace App\Http\Requests\UserInfo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'user_id' => 'required|numeric|exists:App\Models\User,id',
            'avatar' => 'required',
            'front_photo' => 'required',
            'backside_photo' => 'required',
            'sex' => 'required',
            'phone' => 'required|max:15'
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User id is required',
            'user_id.numeric' => 'User id must be type number',
            'user_id.exists' => 'User not exist',
            'avatar.required' => '情報を入力して下さい。',
            'front_photo.required' => '情報を入力して下さい。',
            'backside_photo.required' => '情報を入力して下さい。',
            'sex.required' => '情報を入力して下さい。',
            'note.required' => '情報を入力して下さい。',
            'phone.required' => '情報を入力して下さい。',
            'phone.max' => 'avt_url must be great than 15'
        ];
    }
}
