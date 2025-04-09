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
            'nickname'=>'required|not_regex:/[#&$=\'\"]+/i|exists:users,nickname',
            'password'=>'required',
        ];
    }

    public function messages(): array
    {
        return [
            'nickname.required'=>'Incorrect nickname or password',
            'nickname.not_regex'=>'Incorrect nickname or password',
            'password.required'=>'Incorrect nickname or password',
            'password.exists'=>'Incorrect nickname or password',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors(['error'=>'Incorrect name or password'])->withInput();
    }
}
