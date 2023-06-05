<?php

namespace App\Http\Requests\Product;

use App\Define\CommonDefine;
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
            'title' => 'required|max:255',
            'category_id' => 'required|exists:App\Models\Category,id',
            'type' => 'in:1,2',
            'delete_ids' => 'max:10'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'title 必要です',
            'title.max' => 'title 255文字未満',
            'category_id.required' => 'category id 必要です',
            'category_id.exists' => 'category id 存在しません',
            'file.mimes' => 'file 奇形',
            'tag_name.max' => 'tag name 255文字未満',
        ];
    }
}
