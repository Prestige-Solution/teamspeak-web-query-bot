<?php

namespace App\Http\Requests\Channel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ViewUpsertChannelJobsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'server_id'=>Auth::user()->server_id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'server_id'=>'required|numeric',
            'job_id'=>'sometimes|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required'=>'Hoppla, da lief etwas schief',
            'server_id.numeric'=>'Hoppla, da lief etwas schief',
            'job_id.numeric'=>'Hoppla, da lief etwas schief',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->route('channel.view.channelList')->withErrors($validator)->withInput();
    }
}
