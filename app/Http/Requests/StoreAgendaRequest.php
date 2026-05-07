<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreAgendaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Auth::user()->cannot('create agenda')) {
            Log::warning("Agenda: Unauthorized access attempt to create agenda");
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
            'code' => 'required|string|max:10|unique:agendas,agendaCode',
            'agenda' => 'required|string|max:5000|unique:agendas,agendaDesc',
            'status' => 'required|in:0,1',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Agenda code is required.',
            'code.unique' => 'Agenda code already exists.',
            'agenda.required' => 'Agenda description is required.',
            'agenda.unique' => 'Agenda description already exists.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status value.',
        ];
    }
}
