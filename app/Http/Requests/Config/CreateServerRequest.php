<?php

namespace App\Http\Requests\Config;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateServerRequest extends FormRequest
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
            'ServerName'=>'required',
            'ServerIP' => 'required|unique:ts3_server_configs,server_ip',
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
            'ServerName.required'=>'Bitte gib einen Servernamen an',
            'ServerIP.required' => 'Bitte gib eine IP Adresse an',
            'ServerIP.not_regex' => 'Die IP Adresse enthält nicht erlaubte Zeichen',
            'ServerIP.unique' => 'Die IP Adresse ist bereits vorhanden',
            'QaName.required' => 'Bitte gib einen Query Admin Namen ein',
            'QaName.min'=>'Der Query Admin Name muss mindestens 3 Zeichen enthalten',
            'QaName.not_regex' => 'Der Server Query Admin Name enthält nicht erlaubte Zeichen',
            'QaName.not_in'=>'Der Account serveradmin ist nicht erlaubt',
            'QaPW.required' => 'Bitte gib das Server Query Passwort an',
            'ServerQueryPort.numeric' => 'Der Server Query Port darf nur aus Zahlen bestehen',
            'ServerPort.numeric' => 'Der Server Port darf nur aus Zahlen bestehen',
            'ConMode.required' => 'Hoppla, da lief etwas schief',
            'ConMode.numeric' => 'Hoppla, da lief etwas schief',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
