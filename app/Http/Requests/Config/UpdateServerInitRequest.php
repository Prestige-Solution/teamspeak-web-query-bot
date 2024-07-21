<?php

namespace App\Http\Requests\Config;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServerInitRequest extends FormRequest
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
            'ServerID'=>'required|numeric|exists:ts3_server_configs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ServerID.required'=>'Hoppla, da lief etwas schief',
            'ServerID.numeric'=>'Hoppla, da lief etwas schief',
            'ServerID.exists'=>'Der Server existiert nicht in der Serverliste',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
