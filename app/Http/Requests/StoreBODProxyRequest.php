<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreBODProxyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::user()->can('assign bod proxy')) {

            return true;
        }

        Log::warning("BOD Proxy: Unauthorized access attempt");
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
            'accountToAssign' => 'required|numeric',
            'assignor' => 'required|numeric',
            'assignee' => 'required|numeric',
            'proxyFormNo' => 'required|string|alpha_dash:4,7|unique:proxy_board_of_directors,proxyBodFormNo'
        ];
    }

    public function messages()
    {
        return [
            'accountToAssign.required' => 'The account to assign is required.',
            'assignor.required' => 'The assignor is required.',
            'assignee.required' => 'The assignee is required.',
            'proxyFormNo.required' => 'The proxy form number is required.',
            'proxyFormNo.alpha_dash' => 'The proxy form number must only contain letters, numbers, dashes, and underscores.',
            'proxyFormNo.unique' => 'The proxy form number has already been taken.'
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
