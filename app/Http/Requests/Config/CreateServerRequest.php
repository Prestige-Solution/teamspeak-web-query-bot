<?php

namespace App\Http\Requests\Config;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateServerRequest extends FormRequest
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
        if (str_replace(' ', '', $this->input('qa_nickname') == '')) {
            $this->merge([
                'qa_nickname' => 'web-query-bot',
            ]);
        } else {
            $this->merge([
                'qa_nickname' => str_replace(' ', '', $this->input('qa_nickname')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'server_name'=>'required',
            'server_ip' => 'required|unique:ts3_server_configs,server_ip',
            'qa_name' => 'required|not_in:serveradmin|not_regex:/[#&$=\'\:"]+/i|min:3',
            'qa_pw' => 'required',
            'server_query_port' => 'nullable|numeric',
            'server_port' => 'nullable|numeric',
            'description'=>'nullable',
            'qa_nickname'=>'nullable',
            'mode'=>'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'server_name.required'=>'Enter a server name',
            'server_ip.required' => 'Enter an IP address',
            'server_ip.not_regex' => 'The IP address contains non-permitted characters',
            'server_ip.unique' => 'The IP address already exists',
            'qa_name.required' => 'Enter a query admin name',
            'qa_name.min'=>'The query admin name must contain at least 3 characters',
            'qa_name.not_regex' => 'The server query admin name contains non-permitted characters',
            'qa_name.not_in'=>'The serveradmin account is not allowed',
            'qa_pw.required' => 'Enter the server query password',
            'server_query_port.numeric' => 'The server query port may only consist of numbers',
            'server_port.numeric' => 'The server port may only consist of numbers',
            'mode.required' => 'Oops, something went wrong',
            'mode.numeric' => 'Oops, something went wrong',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
