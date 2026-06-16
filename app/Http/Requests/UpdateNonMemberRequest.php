<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateNonMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {


        if (Auth::user()->cannot('edit non member')) {
            Log::warning('Non-member: Unauthorized access attempt to edit non-member account');
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
        $userId = $this->input('id');
        return [
            'id' => 'required|integer|exists:users,id',
            'email' => 'required|email|unique:users,email,' . $userId,
            'firstName' => 'required|string|max:50',
            'middleName' => 'nullable|string|max:50',
            'lastName' => 'required|string|max:50',
            'status' => 'required|in:0,1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email address.',
            'email.unique' => 'Email already exists.',
            'firstName.required' => 'First name is required.',
            'firstName.max' => 'First name cannot exceed 50 characters.',
            'middleName.max' => 'Middle name cannot exceed 50 characters.',
            'lastName.required' => 'Last name is required.',
            'lastName.max' => 'Last name cannot exceed 50 characters.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active (1) or inactive (0).'
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
