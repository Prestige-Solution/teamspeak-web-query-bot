<?php

namespace App\Http\Requests\Client;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAfkWorkerSettingsRequest extends FormRequest
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
            'MaxIdleTimeSec'=>'required|numeric',
            'AfkChannelCid'=>'required|numeric',
            'ServerGroupSgid'=>'array',
            'AfkWorkerActive'=>'required|numeric',
            'AfkKickClientIdleTime'=>'required|numeric',
            'AfkKickClientSlotsOnline'=>'required|numeric',
            'AfkKickClientsActive'=>'required|numeric',
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
