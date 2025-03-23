<?php

namespace App\Http\Requests\Client;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAfkWorkerSettingsRequest extends FormRequest
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
        return [
            'server_id'=>'required|integer',
            'is_afk_active'=>'required|boolean',
            'max_client_idle_time'=>'required|numeric',
            'afk_channel_cid'=>'required_if:is_afk_active,true|integer',
            'is_afk_kicker_active'=>'required|boolean',
            'afk_kicker_max_idle_time'=>'required|numeric',
            'afk_kicker_slots_online'=>'required|numeric',
            'excluded_servergroup'=>'array',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required'=>'Oops, something went wrong',
            'server_id.integer'=>'Oops, something went wrong',
            'is_afk_active.required'=>'Oops, something went wrong',
            'is_afk_active.boolean'=>'Oops, something went wrong',
            'max_client_idle_time.required'=>'Oops, something went wrong',
            'max_client_idle_time.numeric'=>'Oops, something went wrong',
            'afk_channel_cid.required_if'=>'Please enter an AFK channel',
            'afk_channel_cid.integer'=>'Oops, something went wrong',
            'is_afk_kicker_active.required'=>'Oops, something went wrong',
            'is_afk_kicker_active.boolean'=>'Oops, something went wrong',
            'afk_kicker_max_idle_time.required'=>'Oops, something went wrong',
            'afk_kicker_max_idle_time.numeric'=>'Oops, something went wrong',
            'afk_kicker_slots_online.required'=>'Oops, something went wrong',
            'afk_kicker_slots_online.numeric'=>'Oops, something went wrong',
            'excluded_servergroup.array'=>'Oops, something went wrong',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
