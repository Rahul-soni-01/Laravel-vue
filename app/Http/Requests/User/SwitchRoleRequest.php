<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SwitchRoleRequest extends FormRequest
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
            'role_id' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'role_id.required' => 'Role id is require',
            'role_id.numeric' => 'Role id must be type number'
        ];
    }
}
