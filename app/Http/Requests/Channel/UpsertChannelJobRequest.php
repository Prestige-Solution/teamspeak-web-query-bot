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
            'on_event'=>'required|string',
            'action_id'=>'required|integer',
            'action_user_id'=>'required|integer',
            'channel_cgid'=>'required|integer',
            'channel_template_cid'=>'required|integer',
            'is_notify_message_server_group'=>'required|bool',
            'notify_message_server_group_sgid'=>'required|integer|required_if:is_notify_message_server_group,1',
            'notify_message_server_group_message'=>'nullable',
            'action_min_clients'=>'required|integer|min:1',
            'create_max_channels'=>'required|integer|min:0',
            'is_active'=>'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required'=>'Oops, something went wrong',
            'server_id.integer'=>'Oops, something went wrong!',
            'on_cid.required'=>'Select a channel',
            'on_cid.integer'=>'Oops, something went wrong!',
            'on_event.required'=>'Select an event',
            'on_event.string'=>'Oops, something went wrong!',
            'action_id.required'=>'Select an action',
            'action_id.integer'=>'Oops, something went wrong!',
            'action_user_id.required'=>'Select an client action',
            'action_user_id.integer'=>'Oops, something went wrong!',
            'channel_cgid.required'=>'Select a client action channel group',
            'channel_cgid.integer'=>'Oops, something went wrong!',
            'channel_template_cid.required'=>'Select a channel template',
            'channel_template_cid.integer'=>'Oops, something went wrong!',
            'action_min_clients.required'=>'Enter a valid channel action number of clients',
            'action_min_clients.integer'=>'Oops, something went wrong!',
            'action_min_clients.min'=>'The minimum channel action number of clients is 1',
            'is_notify_message_server_group.required'=>'Select a notify message server group',
            'is_notify_message_server_group.bool'=>'Oops, something went wrong!',
            'notify_message_server_group_sgid.required'=>'Select a server group they will be notified',
            'notify_message_server_group_sgid.integer'=>'Oops, something went wrong!',
            'notify_message_server_group_sgid.required_if'=>'Select a server group they will be notified',
            'create_max_channels.required'=>'Enter a valid max. channels number',
            'create_max_channels.integer'=>'Oops, something went wrong!',
            'create_max_channels.min'=>'The minimum number of channels is 0',
            'is_active.required'=>'Select the status',
            'is_active.boolean'=>'Oops, something went wrong!',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
