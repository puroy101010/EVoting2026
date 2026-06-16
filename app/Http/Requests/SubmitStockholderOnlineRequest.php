<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

            'amendment' => 'required|array',
            'amendment.*.amendmentId' => ['required', 'integer', Rule::exists('amendments')->where('isActive', 1)],
            'amendment.*.yes' => ['boolean'],
            'amendment.*.no' => ['boolean'],

            'agenda' => 'required|array',
            'agenda.*.agendaId' => ['required', 'integer', Rule::exists('agendas')->where('isActive', 1)],
            'agenda.*.favor' => ['boolean'],
            'agenda.*.notFavor' => ['boolean'],
            'agenda.*.abstain' => ['boolean'],

            'bod' => 'required|array',
            'bod.*.candidateId' => ['required', 'integer', Rule::exists('candidates')->where('isActive', 1)],
            'bod.*.vote' => ['nullable', 'integer', 'min:0']
        ];
    }
}
