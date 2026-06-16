<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\DB;


class EApp extends Controller
{

    const SERVER_ERROR = 'Something went wrong. Please contact your administrator if this problem persists';
    const BAD_REQUEST  = 'BAD REQUEST: Please make sure that you filled the required fields.';


    public static function obj_to_array($data)
    {

        try {

            return $data = json_decode(json_encode($data), true);
        } catch (Exception $e) {

            throw new Exception('obeject_to_array');
        }
    }


    public static function clean($string, $bool = false)
    {

        $string = trim($string);

        if ($bool === true) {

            return (strlen($string) === strlen(strip_tags($string))) ? true : false;
        } else {

            return ($string === null or $string === "") ? null : htmlspecialchars($string);
        }
    }


    public static function valid_int($data)
    {

        try {

            $data = trim($data);

            if (preg_match("/[^0-9]/", $data) === 0 and is_numeric($data)) {

                if ((floor($data) - $data) != 0) {

                    throw new Exception("Number is not a whole number");
                }


                if ($data < 1) {

                    throw new Exception("Number is not a positive whole number.");
                }


                return (int) $data;
            }

            if ($data !== "" and $data !== null) {

                throw new Exception("$data : Not a valid whole number");
            }


            $data = (int) $data;
        } catch (Exception $e) {


            throw new Exception($e->getMessage());

            // return response()->json([], 500);

        }
    }



    public static function between_date($start, $end)
    {

        $dateTime = EApp::datetime();

        if (strtotime($dateTime) < strtotime($start) or strtotime($dateTime) > strtotime($end)) {

            return false;
        }

        return true;
    }


    public static function datetime()
    {

        date_default_timezone_set('Asia/Manila');

        return date('Y-m-d G:i:s');
    }


    public static function setting()
    {

        try {


            return DB::table('setting')->first();
        } catch (Exception $e) {

            throw new Exception('EApp@setting');
        }
    }



    public static function generate_id($table = "", $field = "", $length = 0, $pad = 0)
    {

        try {


            $id = DB::table($table)->selectRaw('MAX(CAST(trim(LEADING "0" FROM ' . $field . ') AS UNSIGNED)) AS lastId')->first();


            $id = $id->lastId === null ? 1 : (int)$id->lastId + 1;


            $id = str_pad($id, $length, $pad, STR_PAD_LEFT);

            return $id;
        } catch (Exception $e) {

            throw new Exception('EApp@generate_id');
        }
    }


    public static function integer_to_roman($n)
    {
        // support for numbers greater than a thousand
        $ret1 = '';
        while ($n >= 1000) {
            $ret1 .= 'M';
            $n -= 1000;
        }

        $ret = '';
        if ($n > 0) {
            $n = (string) $n;
            $l = 'IVXLCDM';
            $j = 0; // goes by roman letters
            for ($i = strlen($n) - 1; $i >= 0; --$i) { // goes by decimal number
                switch ($n[$i]) {
                    case 0:
                        $s = '';
                        break;
                    case 1:
                        $s = $l[$j];
                        break;
                    case 2:
                        $s = $l[$j] . $l[$j];
                        break;
                    case 3:
                        $s = $l[$j] . $l[$j] . $l[$j];
                        break;
                    case 4:
                        $s = $l[$j] . $l[$j + 1];
                        break;
                    case 5:
                        $s = $l[$j + 1];
                        break;
                    case 6:
                        $s = $l[$j + 1] . $l[$j];
                        break;
                    case 7:
                        $s = $l[$j + 1] . $l[$j] . $l[$j];
                        break;
                    case 8:
                        $s = $l[$j + 1] . $l[$j] . $l[$j] . $l[$j];
                        break;
                    case 9:
                        $s = $l[$j] . $l[$j + 2];
                        break;
                }
                $j += 2;
                $ret = $s . $ret;
            }
        }

        return $ret1 . $ret;
    }
}
