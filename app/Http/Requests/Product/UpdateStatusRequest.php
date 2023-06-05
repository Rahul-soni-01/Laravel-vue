<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
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
            'product_ids' => 'array',
            'product_ids.*' => 'required|numeric',
            'status' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'product_ids.required' => 'product_ids 必要です',
            'product_ids.array' => 'product_ids 配列でなければなりません',
            'product_ids.*.numeric' => 'product_id 数字でなければなりません',
            'product_ids.*.required' => 'product_id 数字でなければなりません',
            'status.required' => 'status id 必要です',
        ];
    }
}
