<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DestroyStockholderDateSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (!Auth::user()->hasRole('superadmin')) {
            Log::warning("Setting: Unauthorized access attempt to update stockholder voting period by non-superadmin");
            return false;
        }


        if (Auth::user()->cannot('configure stockholder voting period')) {

            Log::warning("Setting: Unauthorized access attempt to update stockholder voting period");
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
            'form' => 'required|in:vote_in_person,vote_by_proxy'
        ];
    }
}
