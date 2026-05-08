<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppHelper
{
    public static function logServerError(string $message, Exception $e, ?array $data = null)
    {

        $erroData = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'data' => $data ?? [],
            'trace' => $e->getTraceAsString(),
            'input' => request()->all(),
            'user_id' => Auth::id(),
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
        ];

        Log::channel('serverError')->error($message, $erroData);
        Log::error('Server Error: ' . $message, $erroData);

        DB::rollback();
    }
}
