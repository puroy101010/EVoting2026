<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditCandidateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {


        if (!Auth::user()->can('edit candidate')) {
            Log::warning("Candidate: Unauthorized access attempt to edit candidate");
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
            'id'          => 'required|numeric|string',
            'last_name'   => 'required|string|max:50',
            'first_name'  => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'type'        => 'required|in:regular,independent',
            'status'      => 'required|in:0,1'
        ];
    }
}
