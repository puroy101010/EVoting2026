<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Services\ConfigService;

class SummaryStockholderOnlineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ballotId' => 'required',

            'amendment' => [
                'array',
                Rule::requiredIf(function () {
                    return ConfigService::getConfig('amendment_enabled') === '1';
                }),
            ],
            'amendment.*.amendmentId' => ['required', 'integer', Rule::exists('amendments')->where('isActive', 1)],
            'amendment.*.yes' => ['boolean'],
            'amendment.*.no' => ['boolean'],

            'agenda' => [
                'array',
                Rule::requiredIf(function () {
                    return ConfigService::getConfig('bod_module_enabled') === '1';
                }),
            ],
            'agenda.*.agendaId' => ['required', 'integer', Rule::exists('agendas')->where('isActive', 1)],
            'agenda.*.favor' => ['boolean'],
            'agenda.*.notFavor' => ['boolean'],
            'agenda.*.abstain' => ['boolean'],

            'bod' => [
                'array',
                Rule::requiredIf(function () {
                    return ConfigService::getConfig('bod_module_enabled') === '1';
                }),
            ],
            'bod.*.candidateId' => ['required', 'integer', Rule::exists('candidates')->where('isActive', 1)],
            'bod.*.vote' => ['nullable', 'integer', 'min:0']
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
