<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreStockholderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::user()->cannot('view stockholder')) {
            Log::warning("Stockholder: Unauthorized access attempt to view stockholder");
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
            'account_number' => [
                'required',
                'string',
                'between:1,4'
            ],
            'stockholder'       => 'required|string|max:100',
            'account_type'      => 'required|in:indv,corp',
            'email'             => 'required|email',
            'vote_in_person'    => 'required|in:stockholder,corp-rep',

            'suffix'            => 'required|numeric|min:1|max:100',
            'delinquent'        => 'required|in:0,1',

            'corp_rep'          => 'nullable|required_with:corp_rep_email|string|max:100',
            'corp_rep_email'    => 'nullable|required_with:corp_rep|email',
            'auth_signatory'    => 'nullable|string|max:200'
        ];
    }


    public function messages()
    {
        return [
            'account_number.required' => 'Account number is required.',
            'account_number.string' => 'Account number must be a string.',
            'account_number.between' => 'Account number must be between 1 and 4 characters.',

            'stockholder.required' => 'Stockholder name is required.',
            'stockholder.string' => 'Stockholder name must be a string.',
            'account_type.required' => 'Account type is required.',
            'account_type.in' => 'Account type must be either "indv" or "corp".',
            'email.required' => 'Email address is required.',
            'vote_in_person.required' => 'Please specify if the stockholder will vote in person or through a corporate representative.',
            'vote_in_person.in' => 'The online voter must be either "stockholder" or "corp-rep".',
            'suffix.required' => 'Suffix is required.',
            'suffix.numeric' => 'Suffix must be a numeric value.',
            'suffix.min' => 'Suffix must be at least 1.',
            'suffix.max' => 'Suffix must not exceed 100.',
            'delinquent.required' => 'Please indicate if the stock is delinquent.',
            'corp_rep.required_with' => 'If a corporate representative email address is provided, their name is also required.',
            'corp_rep_email.required_with' => 'If a corporate representative is provided, their email is also required.'
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

    public function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('You are not authorized to perform this action.');
    }
}
