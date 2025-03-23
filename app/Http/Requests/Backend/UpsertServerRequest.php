<?php

namespace App\Http\Requests\Backend;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpsertServerRequest extends FormRequest
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
            'ServerID'=>'required|numeric',
            'ServerName'=>'required',
            'server_ip' => 'required|not_regex:/[#&$=\'\:"]+/i',
            'QaName' => 'required|not_in:serveradmin|not_regex:/[#&$=\'\:"]+/i|min:3',
            'QaPW' => 'required',
            'ServerQueryPort' => 'nullable|numeric',
            'ServerPort' => 'nullable|numeric',
            'Description'=>'nullable',
            'QueryNickname'=>'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'ServerID.required'=>'Oops, something went wrong',
            'ServerID.numeric'=>'Oops, something went wrong',
            'ServerName.required'=>'Enter a server name',
            'server_ip.required' => 'Enter an IP address',
            'server_ip.not_regex' => 'The IP address contains non-permitted characters',
            'QaName.required' => 'Enter a query admin name',
            'QaName.min'=>'The query admin name must contain at least 3 characters',
            'QaName.not_regex' => 'The server query admin name contains non-permitted characters',
            'QaName.not_in'=>'The serveradmin account is not allowed',
            'QaPW.required' => 'Enter the server query password',
            'ServerQueryPort.numeric' => 'The server query port may only consist of numbers',
            'ServerPort.numeric' => 'The server port may only consist of numbers',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
