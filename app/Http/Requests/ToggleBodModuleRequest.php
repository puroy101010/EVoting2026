<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ToggleBodModuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        if (!Auth::user()->hasRole('superadmin')) {
            Log::warning("Setting: Unauthorized access attempt to Board of Director module setting by non-superadmin");
            return false;
        }
        if (Auth::user()->cannot('configure amendment module')) {
            Log::warning("Setting: Unauthorized configuration attempt to config Board of Director module", ['request' => $this->all()]);
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
