<?php

namespace App\Http\Requests\Channel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateChannelRemoverRequest extends FormRequest
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
            'ChannelCid'=>'required|numeric',
            'MaxIdleTime'=>'required|numeric|min:1',
            'MaxIdleTimeFormat'=>['required',Rule::in(['m','h','d'])],
            'ChannelRemoverActive'=>'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'ServerID.required'=>'Hoppla, da lief etwas schief',
            'ServerID.numeric'=>'Hoppla, da lief etwas schief',
            'ChannelCid.required'=>'Hoppla, da lief etwas schief',
            'ChannelCid.numeric'=>'Hoppla, da lief etwas schief',
            'MaxIdleTime.required'=>'Hoppla, da lief etwas schief',
            'MaxIdleTime.numeric'=>'Hoppla, da lief etwas schief',
            'MaxIdleTime.min'=>'Hoppla, da lief etwas schief',
            'ChannelRemoverActive.required'=>'Hoppla, da lief etwas schief',
            'ChannelRemoverActive.numeric'=>'Hoppla, da lief etwas schief',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
