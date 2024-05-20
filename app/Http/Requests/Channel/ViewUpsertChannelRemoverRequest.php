<?php

namespace App\Http\Requests\Channel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ViewUpsertChannelRemoverRequest extends FormRequest
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
            'server_id'=>'required|numeric',
            'update'=>'sometimes|boolean',
            'remover_id'=>'sometimes|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required' => 'Hoppla, da lief etwas schief',
            'update.bool'=>'Hoppla, da lief etwas schief!',
            'remover_id.numeric'=>'Hoppla, da lief etwas schief!',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
