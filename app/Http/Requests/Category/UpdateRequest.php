<?php

namespace App\Http\Requests\Category;

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
            'name' => 'required|max:255|unique:categories,name,' . $this->id,
            'parent_id' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名前フィールドは必須です',
            'name.max' => '名前フィールドは255文字未満である必要があります',
            'name.unique' => '名前はすでに使われています'
        ];
    }
}
