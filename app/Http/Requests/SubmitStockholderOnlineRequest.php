<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\ConfigService;

class SubmitStockholderOnlineRequest extends FormRequest
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
            'confirmationId' => 'required',

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
}
