<?php

namespace App\Http\Requests\Config;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServerRequest extends FormRequest
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
            'ServerID'=>'required|numeric',
            'ServerName'=>'required',
            'ServerIP' => 'required|unique:ts3_server_configs,server_ip,'.$this->input('ServerID'),
            'QaName' => 'required|not_in:serveradmin|not_regex:/[#&$=\'\:"]+/i|min:3',
            'QaPW' => 'required',
            'ServerQueryPort' => 'nullable|numeric',
            'ServerPort' => 'nullable|numeric',
            'Description'=>'nullable',
            'QueryNickname'=>'nullable',
            'ConMode'=>'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'ServerID.required'=>'Hoppla, da lief etwas schief',
            'ServerID.numeric'=>'Hoppla, da lief etwas schief',
            'ServerName.required'=>'Bitte gib einen Servernamen an',
            'ServerIP.required' => 'Bitte gib eine IP Adresse an',
            'QaName.required' => 'Bitte gib einen Query Admin Namen ein',
            'QaName.min'=>'Der Query Admin Name muss mindestens 3 Zeichen enthalten',
            'QaName.not_regex' => 'Der Server Query Admin Name enthält nicht erlaubte Zeichen',
            'QaName.not_in'=>'Der Account serveradmin ist nicht erlaubt',
            'QaPW.required' => 'Bitte gib das Server Query Passwort an',
            'ServerQueryPort.numeric' => 'Der Server Query Port darf nur aus Zahlen bestehen',
            'ServerPort.numeric' => 'Der Server Port darf nur aus Zahlen bestehen',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->route('serverConfig.view.updateServer')->withErrors($validator)->withInput();
    }
}
