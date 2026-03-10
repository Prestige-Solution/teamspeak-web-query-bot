<?php

namespace App\Http\Requests\Banner;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateUploadedTemplateRequest extends FormRequest
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
            'banner_original_file_name'=>'required|mimes:png',
            'server_id'=>'required|numeric',
            'banner_name' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'banner_original_file_name.required'=>'Select a banner template',
            'banner_original_file_name.mimes'=>'Only PNG banner templates can be uploaded',
            'server_id.required'=>'Oops, something went wrong',
            'server_id.numeric'=>'Oops, something went wrong',
            'banner_name.required'=>'Enter a name for the banner',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
