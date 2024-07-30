<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'full_name' => 'required|min:5|max:150',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                Password::min(5)
                ->mixedCase()
                ->numbers()
                ->symbols(true),
                'confirmed',
            ],
        ];
    }

    /**
     * Get the validation errors messages that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Please enter your name',
            'full_name.min' => 'Name must be atleast 5 chars long',
            'full_name.max' => 'Name must not be more than 150 chars',
            'email.required' => 'Please enter your email address',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email is already taken. Please try with other email address',
            'password.required' => 'Please enter your password',
            'password.string' => 'Password must contain string chars',
            'password.min' => 'Password must be at least 5 chars long',
            'password.confirmed' => 'Password does not match',
            'password.mixedCase' => 'Password must include at least one uppercase and one lowercase letter',
            'password.numbers' => 'Password must include at least one number',
            'password.symbols' => 'Password must include at least one special character',
        ];
    }
}
