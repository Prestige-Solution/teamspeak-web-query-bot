<?php

namespace App\Http\Requests\Channel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DeleteChannelJobRequest extends FormRequest
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
            'JobID'=>'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'JobID.required'=>'Hoppla, da lief etwas schief',
            'JobID.numeric'=>'Hoppla, da lief etwas schief',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator);
    }
}
