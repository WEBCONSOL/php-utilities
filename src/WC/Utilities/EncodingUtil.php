<?php

namespace WC\Utilities;

class EncodingUtil
{
    public static function isBase64Encoded($val): bool {return is_string($val) && base64_encode(base64_decode($val, true)) === $val;}

    public static function isValidJSON($str): bool {
        return $str && is_string($str) && is_array(json_decode($str, true)) && (json_last_error() == JSON_ERROR_NONE);
    }

    public static function isValidMd5($md5 =''): bool{return preg_match('/^[a-f0-9]{32}$/', $md5) ? true : false;}
}