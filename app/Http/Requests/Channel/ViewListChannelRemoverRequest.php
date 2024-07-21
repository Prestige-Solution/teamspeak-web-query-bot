<?php

namespace App\Http\Requests\Channel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ViewListChannelRemoverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ServerID'=>Auth::user()->server_id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ServerID'=>'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'ServerID.required' => 'Hoppla, da lief  etwas schief',
            'ServerID.numeric' => 'Hoppla, da lief  etwas schief',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
