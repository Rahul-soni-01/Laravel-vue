<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'message' => 'string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'message.max' => 'message 255文字未満',
            'file.mimes' => 'file 奇形',
            'url_type.integer' => 'url_type 必要です',
        ];
    }
}
