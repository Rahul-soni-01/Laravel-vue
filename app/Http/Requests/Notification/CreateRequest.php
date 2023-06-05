<?php

namespace App\Http\Requests\Notification;

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
            'content' => 'string|required|max:255',
            'type' => 'numeric|required',
            'user_id' => 'required|exists:App\Models\User,id',
            'post_id' => 'exists:App\Models\Post,id',
            'product_id' => 'exists:App\Models\Product,id',
            'fan_id' => 'exists:App\Models\Fan,id',
        ];
    }

    public function messages()
    {
        return [
            'content.required' => '内容フィールドは必須です',
            'content.max' => '内容 255文字未満',
            'type.required' => 'type 必要です',
            'type.numeric' => 'type 数字でなければなりません',
            'user_id.required' => 'user_id 必要です',
            'user_id.exists' => 'user_id 存在しない',
            'object_type.integer' => 'object type 整数でなければなりません',
            'object_type.required' => 'object type 必要です',
            'object_id.integer' => 'object id 整数でなければなりません',
            'object_id.required' => 'object id 必要です',
        ];
    }
}
