<?php

namespace App\Http\Requests\Backend;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (Auth::check()) {
            return true;
        } else {
            return false;
        }
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
            'CurrentPassword.required'=>'Please enter the current password',
            'CurrentPassword.current_password'=>'The current password is incorrect',
            'NewPassword.required'=>'Please enter a new password',
            'NewPassword.confirmed'=>'The new password does not match',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
