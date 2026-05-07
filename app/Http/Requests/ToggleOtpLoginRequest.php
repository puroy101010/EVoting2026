<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ToggleOtpLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (!Auth::user()->hasRole('superadmin')) {
            Log::warning("Setting: Unauthorized access attempt to update OTP login setting by non-superadmin");
            return false;
        }

        if (Auth::user()->cannot('configure otp login')) {

            Log::warning('Setting: Unauthorized access attempt to configure OTP login', ['enabled' => $this->enabled]);
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
