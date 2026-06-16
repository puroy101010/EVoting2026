<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PreviewBallotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        if (Auth::user()->can('view ballot details') || Auth::user()->role === 'superadmin') {
            return true;
        }
        Log::warning("Preview Ballot: Unauthorized access attempt", [
            'role' => Auth::user()->role,
            'url' => $this->url()
        ]);

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }


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
