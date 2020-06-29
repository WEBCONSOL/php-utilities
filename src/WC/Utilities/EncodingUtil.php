<?php

namespace WC\Utilities;

class EncodingUtil
{
    private static $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';

    public static function isBase64Encoded($val): bool {
        return is_string($val) && base64_encode(base64_decode($val, true)) === $val;
    }

    public static function isValidJSON($str): bool {
        return $str && is_string($str) && is_array(json_decode($str, true)) && (json_last_error() == JSON_ERROR_NONE);
    }

    public static function isValidMd5($md5 =''): bool {
        return preg_match('/^[a-f0-9]{32}$/', $md5) === 1;
    }

    public static final function uuid(): string {return strtoupper(exec('uuidgen'));}

    public static final function isValidUUID(string $id): bool {
        return preg_match(self::$UUIDv4, $id) === 1;
    }
}