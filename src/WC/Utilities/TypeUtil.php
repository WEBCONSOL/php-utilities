<?php

namespace WC\Utilities;

class TypeUtil
{
    public static function castToType($value, $type) {

        switch($type)
        {
            case "boolean":
                return (bool) $value;

            case "integer":
                return (int) $value;

            case "double":
                return (double) $value;

            case "string":
                return (string) $value;

            case "array":
                if (is_array($value)) {
                    return $value;
                }
                else if (is_object($value)) {
                    return json_decode(json_encode($value), true);
                }
                else if (EncodingUtil::isValidJSON($value)) {
                    return json_decode($value, true);
                }
                return (array) $value;

            case "object":
                if (is_object($value)) {
                    return $value;
                }
                else if (is_array($value)) {
                    return json_decode(json_encode($value));
                }
                else if (EncodingUtil::isValidJSON($value)) {
                    return json_decode($value);
                }
                return (object) $value;

            case "resource":
            case "null":
            default:
                return $value;
        }
    }
}