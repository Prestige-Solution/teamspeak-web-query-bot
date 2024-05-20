<?php

namespace App\Http\Requests\Client;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePoliceWorkerSettingsRequest extends FormRequest
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
            'DiscordWebhookActive'=>'required|numeric',
            'DiscordWebhookUrl'=>'nullable|url',
            'PoliceCheckBotAlive'=>'required|numeric',
            'PoliceVpnProtection'=>'required|numeric',
            'AllowVpnForServerGroup'=>'required|numeric',
            'PoliceAutoupdateChannels'=>'required|boolean',
            'PoliceDeleteClientsOfflineTime'=>'required|numeric',
            'PoliceDeleteClientsTimeType'=>'required|numeric',
            'PoliceDeleteClientsActive'=>'required|boolean',
            'PoliceBadNames'=>'required|boolean',
            'PoliceBadNamesGlobalList'=>'required|boolean',
        ];
    }

    public function messages(): array
    {
        //TODO create messages
        return [

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
