<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest {

    public function rules(): array {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array {
        return [
            'current_password.required' => 'You must enter your current password.',
            'new_password.required' => 'You must enter a new password.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.min' => 'The new password must be at least 8 characters.',
        ];
    }
}
