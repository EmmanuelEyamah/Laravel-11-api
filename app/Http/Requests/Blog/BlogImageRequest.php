<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class BlogImageRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'required|mimes:png,jpg,jpeg,gif',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'The image field is required.',
            'image.string' => 'The image must be a png,jpg,jpeg or gif.',
        ];
    }
}
