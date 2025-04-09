<?php

namespace App\Http\Requests\Banner;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpsertConfigBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (Auth::check()) {
            return true;
        }else
        {
            return false;
        }
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'delay'=>(int)$this->input('delay')
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:banners,id',
            'coord_x'=>'required',
            'coord_y'=>'required',
            'font_id'=>'integer|exists:cat_fonts,id',
            'font_size'=>'integer',
            'color_hex'=>'hex_color',
            'delay'=>'integer|min:1',
            'option_id'=>'nullable',
            'extra_option'=>'nullable',
            'text'=>'nullable',
            'banner_hostbanner_url'=>'nullable|url',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'=>'Oops, something went wrong',
            'id.integer'=>'Oops, something went wrong',
            'id.exists'=>'The banner could not be found',
            'coord_x.required'=>'Enter a X coordinate',
            'coord_y.required'=>'Enter a Y coordinate',
            'font_id.integer'=>'Oops, something went wrong',
            'font_id.exists'=>'The font could not be found',
            'font_size.integer'=>'Enter a valid font size',
            'color_hex'=>'Select a valid color',
            'delay.integer'=>'Enter a valid delay',
            'delay.min'=>'The minimum delay value is 1',
            'banner_hostbanner_url.url'=>'Enter a valid hostbanner URL',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
