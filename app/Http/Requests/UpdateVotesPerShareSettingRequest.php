<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateVotesPerShareSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (!Auth::user()->hasRole('superadmin')) {
            Log::warning("Setting: Unauthorized access attempt to update votes per share setting by non-superadmin");
            return false;
        }

        if (Auth::user()->cannot('configure number of vote per share')) {
            Log::warning("Setting: Unauthorized access attempt to update votes per share setting");
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
            'votes_per_share' => 'required|integer|min:1|max:50'
        ];
    }
}
