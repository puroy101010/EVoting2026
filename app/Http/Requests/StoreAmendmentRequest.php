<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreAmendmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {


        if (Auth::user()->cannot('create amendment')) {
            Log::warning("Amendment: Unauthorized access attempt to create amendment");
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
            'code' => 'required|string|max:10|unique:amendments,amendmentCode',
            'amendment' => 'required|string|max:5000|unique:amendments,amendmentDesc',
            'status' => 'required|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Amendment code is required.',
            'code.unique' => 'Amendment code already exists.',
            'amendment.required' => 'Amendment description is required.',
            'amendment.unique' => 'Amendment already exists.',
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
