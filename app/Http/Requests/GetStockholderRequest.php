<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GetStockholderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {


        if (Auth::user()->can('view stockholder')) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'accounts'          => 'nullable|between:4,4',
            'account_type'      => 'nullable|in:indv,corp',
            'status'            => 'nullable|in:0,1',
            'proxy'             => 'nullable:in,0,1',
            'proxy_assignee'    => 'nullable:in,0,1',
            'role'              => 'nullable|in:stockholder,corp-rep',
            'per_page'          => 'nullable|numeric|integer|min:1|max:4000',

        ];
    }



    public function messages()
    {
        return [
            'accounts.between' => 'The accounts field must be exactly 4 characters.',
            'account_type.in'  => 'The selected account type is invalid.',
            'status.in'        => 'The selected status is invalid.',
            'proxy.in'         => 'The selected proxy is invalid.',
            'proxy_assignee.in' => 'The selected proxy assignee is invalid.',
            'role.in'         => 'The selected role is invalid.',
            'per_page.min'    => 'The per page must be at least 1.',
            'per_page.max'    => 'The per page may not be greater than 4000.',
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
