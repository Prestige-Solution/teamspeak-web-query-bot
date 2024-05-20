<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AuthenticateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'NickName'=>'required|not_regex:/[#&$=\'\"]+/i',
            'Password'=>'required',
        ];
    }

    public function messages(): array
    {
        return [
            'NickName.required'=>'Name oder Passwort falsch',
            'NickName.not_regex'=>'Name oder Passwort falsch',
            'Password.required'=>'Name oder Passwort falsch',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
