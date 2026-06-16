<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCorporateRepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        if (Auth::user()->can('edit corporate representative') or Auth::user()->role('superadmin')) {
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
            'corp_rep' => 'nullable|required_with:corp_rep_email|string|min:2|max:100',
            'corp_rep_email' => 'nullable|required_with:corp_rep|email|max:100'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'corp_rep.required_with' => 'Corporate representative name is required when email is provided.',
            'corp_rep.string' => 'Corporate representative name must be a valid string.',
            'corp_rep.min' => 'Corporate representative name must be at least 2 characters.',
            'corp_rep.max' => 'Corporate representative name cannot exceed 100 characters.',
            'corp_rep_email.required_with' => 'Corporate representative email is required when name is provided.',
            'corp_rep_email.email' => 'Corporate representative email must be a valid email address.',
            'corp_rep_email.max' => 'Corporate representative email cannot exceed 100 characters.'
        ];
    }
}
