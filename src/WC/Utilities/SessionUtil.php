<?php

namespace WC\Utilities;

class SessionUtil
{
    public static function toSessionValue($val): string {
        if (is_string($val)) {
            if (!EncodingUtil::isBase64Encoded($val)) {
                return base64_encode($val);
            }
        }
        else if (is_array($val) || is_object($val)) {
            return base64_encode(serialize($val));
        }
        return $val;
    }

    public static function fromSessionValue($val) {
        if (is_string($val)) {
            if (EncodingUtil::isBase64Encoded($val)) {
                $val = base64_decode($val);
                if (@unserialize($val)) {
                    return unserialize($val);
                }
                else if (EncodingUtil::isValidJSON($val)) {
                    return json_decode($val);
                }
            }
        }
        return $val;
    }
}