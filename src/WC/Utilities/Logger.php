<?php

namespace WC\Utilities;

class Logger
{
    private function __construct(){}

    public static function log($message, $message_type = null, $destination = null, $extra_headers = null) {
        error_log($message, $message_type, $destination, $extra_headers);
    }

    public static function error($message, $message_type = null, $destination = null, $extra_headers = null) {
        if (!defined('ERROR_REPORTING_NO_ERROR')) {self::log('[error] '.$message, $message_type, $destination, $extra_headers);}
    }

    public static function debug($message, $message_type = null, $destination = null, $extra_headers = null) {
        if (!defined('ERROR_REPORTING_NO_DEBUG')) {self::log('[debug] '.$message, $message_type, $destination, $extra_headers);}
    }

    public static function info($message, $message_type = null, $destination = null, $extra_headers = null) {
        if (!defined('ERROR_REPORTING_NO_INFO')) {self::log('[info] '.$message, $message_type, $destination, $extra_headers);}
    }

    public static function warning($message, $message_type = null, $destination = null, $extra_headers = null) {
        if (!defined('ERROR_REPORTING_NO_WARNING')) {self::log('[warning] '.$message, $message_type, $destination, $extra_headers);}
    }
}