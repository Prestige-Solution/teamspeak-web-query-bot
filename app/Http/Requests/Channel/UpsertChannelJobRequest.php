<?php

namespace App\Http\Requests\Channel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpsertChannelJobRequest extends FormRequest
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
            'on_cid'=>'required|integer',
            'on_event'=>'required',
            'action_id'=>'required|integer',
            'action_user_id'=>'required|integer',
            'channel_cgid'=>'required|integer',
            'channel_template_id'=>'required|integer',
            'is_notify_message_server_group'=>'required|bool',
            'notify_message_server_group_sgid'=>'required|integer',
            'notify_message_server_group_message'=>'nullable',
            'action_min_clients'=>'required|integer',
            'create_max_channels'=>'required|integer',
            'is_active'=>'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            //TODO extend messages
            'on_cid.required'=>'Select a channel',
            'on_event.required'=>'Select an event',
            'action_id.required'=>'Select an action',
            'action_min_clients.required'=>'Select a client action',
            'channel_cgid.required'=>'Select a Client Channel Group',
            'channel_template_id.required'=>'Select a channel template',
            'server_id.required'=>'Oops, something went wrong',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
