<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ];

        if (filled($this->user()?->password)) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        return $rules;
    }
}
