<?php

namespace App\Http\Requests\Channel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateChannelRemoverRequest extends FormRequest
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
            'server_id'=>'required|integer',
            'channel_cid'=>'required|integer',
            'channel_max_seconds_empty'=>'required|integer|min:1',
            'channel_max_time_format'=>['required', Rule::in(['m', 'h', 'd'])],
            'is_active'=>'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'server_id.required'=>'Oops, something went wrong',
            'server_id.integer'=>'Oops, something went wrong',
            'channel_cid.required'=>'Choose a channel',
            'channel_cid.integer'=>'Oops, something went wrong',
            'channel_max_seconds_empty.required'=>'Oops, something went wrong',
            'channel_max_seconds_empty.integer'=>'Oops, something went wrong',
            'channel_max_seconds_empty.min'=>'Oops, something went wrong',
            'channel_max_time_format.required'=>'Select a time format (minute, hour, day)',
            'is_active.required'=>'Oops, something went wrong',
            'is_active.boolean'=>'Oops, something went wrong',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
