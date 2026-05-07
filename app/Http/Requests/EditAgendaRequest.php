<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EditAgendaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (Auth::user()->cannot('edit agenda')) {
            Log::warning("Agenda: Unauthorized access attempt to edit agenda");
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
            'id' => 'required|exists:agendas,agendaId',
            'agenda' => [
                'required',
                'string',
                'max:5000',
                Rule::unique('agendas', 'agendaDesc')
                    ->ignore($this->id, 'agendaId')
            ],
            'status' => 'required|in:0,1',
        ];
    }
}
