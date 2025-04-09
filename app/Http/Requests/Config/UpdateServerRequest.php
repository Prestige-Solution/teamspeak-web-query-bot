<?php

namespace App\Http\Requests\Config;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateServerRequest extends FormRequest
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
                'server_id' => Auth::user()->default_server_id,
            ]);
        } else {
            $this->merge([
                'qa_nickname' => str_replace(' ', '', $this->input('qa_nickname')),
                'server_id' => Auth::user()->default_server_id,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'server_id'=>'required|integer|exists:ts3_server_configs,id',
            'server_name'=>'required',
            'server_ip' => 'required|unique:ts3_server_configs,server_ip,'.$this->input('server_id'),
            'qa_name' => 'required|not_in:serveradmin|not_regex:/[#&$=\'\:"]+/i|min:3',
            'qa_pw' => 'required',
            'server_query_port' => 'nullable|integer',
            'server_port' => 'nullable|integer',
            'description'=>'nullable',
            'qa_nickname'=>'nullable',
            'mode'=>'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required'=>'Oops, something went wrong',
            'server_id.integer'=>'Oops, something went wrong',
            'server_id.exists'=>'The server could not be found',
            'server_name.required'=>'Enter a server name',
            'server_ip.required' => 'Enter an IP address',
            'qa_name.required' => 'Enter a query admin name',
            'qa_name.min'=>'The query admin name must contain at least 3 characters',
            'qa_name.not_regex' => 'The server query admin name contains non-permitted characters',
            'qa_name.not_in'=>'The serveradmin account is not allowed',
            'qa_pw.required' => 'Enter the server query user password for "'.$this->input('server_name').'"',
            'server_query_port.integer' => 'The server query port may only consist of numbers',
            'server_port.integer' => 'The server port may only consist of numbers',
            'mode.required'=>'Oops, something went wrong',
            'mode.integer'=>'Oops, something went wrong',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->route('serverConfig.view.serverList')->withErrors($validator)->withInput();
    }
}
