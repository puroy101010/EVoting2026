<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Configuration;
use Illuminate\Support\Facades\Log;

class AppController extends Controller
{

    public $allowedMimes = "";


    public static function record_exist($query, $param)
    {

        $result = DB::select($query, $param);

        if (count($result) > 0) {

            return true;
        }

        return false;
    }




    public static function contain_html_tag($string)
    {

        return preg_match("/<[^<]+>/", $string, $m) != 0;
    }



    /**
     * Retrieve application settings from the configuration table
     * 
     * @param string|null $meta Specific configuration key to retrieve
     * @return array|Configuration|null Returns array of all configs, specific config object, or null if not found
     * @throws Exception When configuration retrieval fails
     */
    public static function app_setting($meta = null)
    {

        // If requesting a specific configuration
        if ($meta !== null) {
            $config = Configuration::where('config', $meta)->first();

            if (!$config) {
                Log::warning("Configuration key '{$meta}' not found.");
                throw new Exception("Configuration key '{$meta}' not found.");
            }

            // Return the value directly if config exists, null otherwise
            return $config->toArray();
        }


        $configurations = Configuration::select('config', 'value')->get();

        // Build associative array using pluck for better performance
        $cachedConfigs = $configurations->pluck('value', 'config')->toArray();

        return $cachedConfigs;
    }
}
