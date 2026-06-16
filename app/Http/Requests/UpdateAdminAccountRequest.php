<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateAdminAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        $user = Auth::user();
        $requestedRole = $this->input('role');

        // Only superadmin can create another superadmin
        if (($requestedRole === 'superadmin' or $user->hasRole('superadmin')) and !$user->hasRole('superadmin')) {

            Log::info("Action blocked: Admin lacks superadmin role required to update or create a superadmin account.");
            return false;
        }

        // Must have permission to edit admin accounts
        if ($user->cannot('edit admin account')) {
            Log::info("Action blocked: Admin lacks 'edit admin account' role: {$user->id}");
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'                => 'required|integer|exists:users,id',
            'firstName'        => 'required|string|max:50',
            'middleName'        => 'nullable|string|max:50',
            'lastName'        => 'required|string|max:50',
            'role'             => 'required|string|in:superadmin,admin,audit,delinquent,member,encoder',
            'status'            => 'required|in:0,1'
        ];
    }


    /**
     * Get custom error messages for validator.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'firstName.required' => 'First name is required.',
            'firstName.max' => 'First name cannot exceed 50 characters.',
            'firstName.string' => 'First name must be a string.',
            'middleName.max' => 'Middle name cannot exceed 50 characters.',
            'middleName.string' => 'Middle name must be a string.',
            'lastName.required' => 'Last name is required.',
            'lastName.max' => 'Last name cannot exceed 50 characters.',
            'lastName.string' => 'Last name must be a string.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email address.',
            'role.required' => 'Role is required.',
            'role.string' => 'Role must be a string.',
            'role.in' => 'Role must be one of the following: superadmin, admin, membership, auditor, encoder.',
        ];
    }


    /**
     * Override failed validation to return only the first error and its field.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $firstField = array_key_first($errors);
        $firstMessage = $errors[$firstField][0] ?? 'Validation failed!';

        Log::error('Validation failed', [
            'message' => $firstMessage,
            'field' => $firstField,
            'errors' => $errors,
            "request" => $this->all(),
            "url" => $this->url()
        ]);
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $firstMessage,
            'field' => $firstField,
            'errors' => $errors
        ], 422));
    }
}
