<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ToggleVotingReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (!Auth::user()->hasRole('superadmin')) {
            Log::warning("Setting: Unauthorized access attempt to update voting confirmation receipt by non-superadmin");
            return false;
        }

        if (Auth::user()->cannot('configure voting confirmation receipt')) {
            Log::warning("Setting: Unauthorized configuration attempt to config voting confirmation receipt", ['request' => $this->all()]);
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
            'enabled' => 'required|in:true,false'
        ];
    }
}
