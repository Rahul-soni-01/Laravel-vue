<?php

namespace App\Http\Requests\MessageDetail;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequestMessageDetail extends FormRequest
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
            'message_id' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'message.max' => 'message 255文字未満',
            'receiver_id.required' => 'receiver id 必要です',
            'receiver_id.exists' => 'receiver id 存在しません',
            'file.mimes' => 'file 奇形',
            'url_type.integer' => 'url_type 必要です',
        ];
    }
}
