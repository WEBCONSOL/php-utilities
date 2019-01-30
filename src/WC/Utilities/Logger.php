<?php

namespace WC\Utilities;

class Logger
{
    private function __construct(){}

    public static function log($message, $message_type = null, $destination = null, $extra_headers = null) {
        error_log($message, $message_type, $destination, $extra_headers);
    }
}