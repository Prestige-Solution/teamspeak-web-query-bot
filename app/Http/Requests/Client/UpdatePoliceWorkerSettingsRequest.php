<?php

namespace App\Http\Requests\Client;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePoliceWorkerSettingsRequest extends FormRequest
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
            'server_id'=>Auth::user()->default_server_id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'server_id'=>'required|integer|exists:ts3_server_configs,id',
            'is_discord_webhook_active'=>'required|boolean',
            'discord_webhook_url'=>'nullable|url',
            'is_check_bot_alive_active'=>'required|boolean',
            'is_vpn_protection_active'=>'required|boolean',
            'vpn_protection_api_register_mail'=>'nullable|email|required_if_accepted:is_vpn_protection_active',
            'allow_sgid_vpn'=>'required|integer',
            'is_channel_auto_update_active'=>'required|boolean',
            'is_bad_name_protection_active'=>'required|boolean',
            'is_bad_name_protection_global_list_active'=>'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required'=>'Oops, something went wrong',
            'server_id.integer'=>'Oops, something went wrong',
            'server_id.exists'=>'Oops, something went wrong',
            'is_discord_webhook_active.required'=>'Oops, something went wrong',
            'is_discord_webhook_active.boolean'=>'Oops, something went wrong',
            'is_check_bot_alive_active.required'=>'Oops, something went wrong',
            'is_check_bot_alive_active.boolean'=>'Oops, something went wrong',
            'is_vpn_protection_active.required'=>'Oops, something went wrong',
            'is_vpn_protection_active.boolean'=>'Oops, something went wrong',
            'vpn_protection_api_register_mail.email'=>'Oops, something went wrong',
            'vpn_protection_api_register_mail.required_if_accepted'=>'Enter an e-mail address for the API function',
            'allow_sgid_vpn.required'=>'Oops, something went wrong',
            'allow_sgid_vpn.integer'=>'Oops, something went wrong',
            'is_channel_auto_update_active.required'=>'Oops, something went wrong',
            'is_channel_auto_update_active.boolean'=>'Oops, something went wrong',
            'is_bad_name_protection_active.required'=>'Oops, something went wrong',
            'is_bad_name_protection_active.boolean'=>'Oops, something went wrong',
            'is_bad_name_protection_global_list_active.required'=>'Oops, something went wrong',
            'is_bad_name_protection_global_list_active.boolean'=>'Oops, something went wrong',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
