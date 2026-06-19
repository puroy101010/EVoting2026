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


    /**
     * Compare two strings with optional case-sensitivity and trimming.
     * 
     * Handles null values gracefully:
     * - Both null returns true
     * - One null returns false
     * - Trims whitespace from both strings before comparison
     * 
     * @param string|null $str1 First string to compare
     * @param string|null $str2 Second string to compare
     * @param bool $caseSensitive Whether to perform case-sensitive comparison (default: false)
     * @return bool True if strings match according to comparison rules
     */
    public static function compareStrings(?string $str1, ?string $str2, bool $caseSensitive = false): bool
    {
        // Normalize: trim and convert empty strings to null
        $string1 = blank($str1) ? null : trim($str1);
        $string2 = blank($str2) ? null : trim($str2);

        // Both null is considered equal
        if ($string1 === null && $string2 === null) {
            return true;
        }

        // One null and one not is not equal
        if ($string1 === null || $string2 === null) {
            return false;
        }

        // Compare with case sensitivity setting
        return $caseSensitive
            ? $string1 === $string2
            : strtolower($string1) === strtolower($string2);
    }

    public static function normalizeEmail(?string $email, bool $toLowercase = true): ?string
    {
        if (blank($email)) {
            return null;
        }

        $email = trim($email);
        return $toLowercase ? strtolower($email) : $email;
    }


    public static function normalizeString(?string $text, bool $toLowercase = false): ?string
    {
        if (blank($text)) {
            return null;
        }

        $text = trim($text);
        return $toLowercase ? strtolower($text) : $text;
    }
}
