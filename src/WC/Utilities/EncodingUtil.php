<?php

namespace WC\Utilities;

class EncodingUtil
{
    public static function isBase64Encoded($val): bool {return base64_encode(base64_decode($val, true)) === $val;}

    public static function isValidJSON(string $str): bool {
        if ($str) {
            $str = trim($str);
            $first = $str[0];
            $last = $str[strlen($str) - 1];
            if (($first === "{" && $last === "}") || ($first === "[" && $last === "]")) {
                if (is_string($str) && is_array(json_decode($str, true)) && (json_last_error() == JSON_ERROR_NONE)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function isValidMd5($md5 =''): bool{return preg_match('/^[a-f0-9]{32}$/', $md5) ? true : false;}
}