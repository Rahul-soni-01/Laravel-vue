<?php

namespace App\Http\Requests\Fan;

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
            'title' => 'required|max:255',
            'nickname' => 'required|max:100|unique:fans,nickname,' . $this->id,
            'category_id' => 'exists:App\Models\Category,id',
            'photo' => 'mimes:jpg,png,gif,jpeg,mp4,mov,mkv',
            'avt' => 'mimes:jpg,png,gif,jpeg,mp4,mov,mkv',
            'background' => 'mimes:jpg,png,gif,jpeg,mp4,mov,mkv'
        ];
    }

    public function messages()
    {
        return [
            'nickname.unique' => 'ニックネームはすでに取られています。'
        ];
    }
}
