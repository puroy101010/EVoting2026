<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAuthSignatoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        if (Auth::user()->can('edit authorized signatory') or Auth::user()->role('superadmin')) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'auth_signatory' => 'nullable|required_with:auth_signatory_email|string|min:2|max:100',
            'auth_signatory_email' => 'nullable|required_with:auth_signatory|email|max:100'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'auth_signatory.required_with' => 'Authorized signatory name is required when email is provided.',
            'auth_signatory.string' => 'Authorized signatory name must be a valid string.',
            'auth_signatory.min' => 'Authorized signatory name must be at least 2 characters.',
            'auth_signatory.max' => 'Authorized signatory name cannot exceed 100 characters.',
            'auth_signatory_email.required_with' => 'Authorized signatory email is required when name is provided.',
            'auth_signatory_email.email' => 'Authorized signatory email must be a valid email address.',
            'auth_signatory_email.max' => 'Authorized signatory email cannot exceed 100 characters.'
        ];
    }
}
