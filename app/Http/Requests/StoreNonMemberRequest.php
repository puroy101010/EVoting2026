<?php

namespace App\Http\Requests;

use App\Services\NonMemberService;
use App\Services\UtilityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreNonMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::user()->cannot('create non member')) {
            Log::warning('Non-member: Unauthorized access attempt to create non-member account');
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
            'email' => 'required|email|unique:users,email',
            'account_number' => [
                'required',
                'string',
                'min:4',
                'max:8',
                function ($attribute, $value, $fail) {
                    if (UtilityService::isAccountNumberTaken($value)) {
                        $fail('The account number is already used in another account.');
                    }
                },
            ],
            'firstName' => 'required|string|max:50',
            'middleName' => 'nullable|string|max:50',
            'lastName' => 'required|string|max:50',
            'isGM' => 'required|in:0,1',
            'status' => 'required|in:0,1',
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
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'account_number.required' => 'Account number is required.',
            'account_number.min' => 'Account number must be at least 4 digits.',
            'account_number.max' => 'Account number must not exceed 8 digits.',
            'firstName.required' => 'First name is required.',
            'firstName.max' => 'First name cannot exceed 50 characters.',
            'middleName.max' => 'Middle name cannot exceed 50 characters.',
            'lastName.required' => 'Last name is required.',
            'lastName.max' => 'Last name cannot exceed 50 characters.',
            'isGM.required' => 'GM status is required.',
            'isGM.in' => 'GM status must be either Yes or No.',
            'status.required' => 'Account status is required.',
            'status.in' => 'Status must be either Active or Inactive.'
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
