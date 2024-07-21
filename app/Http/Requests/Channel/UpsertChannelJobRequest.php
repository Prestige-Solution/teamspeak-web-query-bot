<?php

namespace App\Http\Requests\Channel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpsertChannelJobRequest extends FormRequest
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
            'ChannelTarget'=>'required|numeric',
            'ChannelEvent'=>'required',
            'ChannelAction'=>'required|numeric',
            'ChannelActionUserInChannel'=>'required|numeric',
            'ChannelActionUserInChannelGroup'=>'required|numeric',
            'ChannelTemplate'=>'required|numeric',
            'NotifyServerGroupBool'=>'required|bool',
            'NotifyServerGroupSgid'=>'required|numeric',
            'NotifyServerGroupMessage'=>'nullable',
            'ChannelActionMinClientCount'=>'required|numeric',
            'MaxChannels'=>'required|numeric',
        ];
    }

    public function messages(): array
    {
        //TODO create messages
        return [
            'ChannelTarget.required'=>'Bitte wähle einen Channel aus',
            'ChannelEvent.required'=>'Bitte wähle ein Ereignis aus',
            'ChannelAction.required'=>'Bitte wähle ein Aktion aus',
            'ChannelActionUserInChannel.required'=>'Bitte wähle eine Client Aktion aus',
            'ChannelActionUserInChannelGroup.required'=>'Bitte wähle eine Client Channel Group aus',
            'ChannelTemplate.required'=>'Bitte wähle ein Channel Template aus',
            'ServerID.required'=>'Hoppla, da lief etwas schief',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
