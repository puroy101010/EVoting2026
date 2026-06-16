<?php

namespace App\Services;


use App\Models\Configuration;

use Exception;

use Illuminate\Support\Facades\Log;

class ConfigService
{


    public static function getConfig($meta = null)
    {


        // If requesting a specific configuration
        if ($meta !== null) {
            $config = Configuration::where('config', $meta)->first();

            if (!$config) {
                Log::warning("Configuration key '{$meta}' not found.");
                throw new Exception("Configuration key '{$meta}' not found.");
            }

            // Return the value directly if config exists, null otherwise
            return $config->value;
        }


        $configurations = Configuration::select('config', 'value')->get();

        // Build associative array using pluck for better performance
        $cachedConfigs = $configurations->pluck('value', 'config')->toArray();

        return $cachedConfigs;
    }
}
