<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UpdateAmendmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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

            'id' => 'required|exists:amendments,amendmentId',
            'amendment' => [
                'required',
                'string',
                'max:5000',
                Rule::unique('amendments', 'amendmentDesc')
                    ->ignore($this->id, 'amendmentId')
            ],
            'status' => 'required|in:0,1',

        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Amendment ID is required.',
            'id.exists' => 'The specified amendment does not exist.',
            'amendment.required' => 'Amendment description is required.',
            'amendment.unique' => 'Amendment description already exists.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status value provided.'
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
