<?php

namespace App\Http\Requests\Backend;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateChangePasswordRequest extends FormRequest
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
            'CurrentPassword'=>'required|current_password',
            'NewPassword'=>'required|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'CurrentPassword.required'=>'Bitte gib das aktuelle Passwort ein',
            'CurrentPassword.current_password'=>'Das aktuelle Passwort ist nicht korrekt',
            'NewPassword.required'=>'Bitte gib das neue Passwort ein',
            'NewPassword.confirmed'=>'Das neue Passwort stimmt nicht überein',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
