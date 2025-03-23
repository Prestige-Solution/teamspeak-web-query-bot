<?php

namespace App\Http\Requests\Ts3Config;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateNewBadNameRequest extends FormRequest
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'server_id' => Auth::user()->default_server_id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $value = $this->input('Value');

        return [
            'server_id' => 'required|integer|exists:ts3_server_configs,id',
            'description'=>'required',
            'value_option'=>'required|numeric',
            'value'=>['required'], [
                Rule::unique('bad_names')->where(function ($query) use ($value) {
                    return $query->where('value', '=', $value)
                        ->where('server_id', '=', Auth::user()->default_server_id);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        //TODO create messages
        return [
            'Value.unique'=>'The entered value already exists',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
