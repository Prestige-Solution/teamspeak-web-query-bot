<?php

namespace App\Http\Requests\Backend;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ViewUpdateServerRequest extends FormRequest
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
            'server_id'=>'sometimes|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required'=>'Hoppla, da lief etwas schief',
            'server_id.numeric'=>'Hoppla, da lief etwas schief',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->route('backend.view.createOrUpdateServer')->withErrors($validator)->withInput();
    }
}
