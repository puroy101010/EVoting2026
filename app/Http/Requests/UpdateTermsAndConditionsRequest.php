<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateTermsAndConditionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (!Auth::user()->hasRole('superadmin')) {
            Log::warning("Setting: Unauthorized access attempt to update terms and conditions by non-superadmin");
            return false;
        }

        if (Auth::user()->cannot('update terms and conditions')) {
            Log::warning("Terms and Conditions: Unauthorized access attempt to update terms and conditions");
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
            'type' => 'required|string|in:online,proxy',
            'content' => 'required|string',
        ];
    }
}
