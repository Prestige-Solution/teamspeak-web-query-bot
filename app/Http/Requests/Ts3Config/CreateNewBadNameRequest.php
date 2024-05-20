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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $value = $this->input('Value');

        return [
            'NameDescription'=>'required',
            'ProofOption'=>'required|numeric',
            'Value'=>['required'],[
                Rule::unique('bad_names')->where(function ($query) use($value){
                    return $query->where('value','=',$value)
                        ->where('server_id','=',Auth::user()->server_id);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        //TODO create messages
        return [
            'Value.unique'=>'Der Eingegebene Wert existiert bereits',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
