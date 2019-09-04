<?php

namespace WC\Utilities;

class Logger
{
    private static $DEBUG = false;

    private function __construct(){}

    public static function log($message, $message_type = null, $destination = null, $extra_headers = null) {
        error_log($message, $message_type, $destination, $extra_headers);
    }

    public static function error($message, $message_type = null, $destination = null, $extra_headers = null) {
        if (!defined('ERROR_REPORTING_NO_ERROR')) {
            $calledIn = self::generateCallTrace(1);
            if (!is_string($message) && method_exists($message, 'getMessage')) {
                $message = $message->getMessage().' at '.$message->getFile().' on line '.$message->getLine();
            }
            self::log(($calledIn?$calledIn.' - ':'').'[error] '.$message, $message_type, $destination, $extra_headers);
        }
    }

    public static function debug($message, $message_type = null, $destination = null, $extra_headers = null) {
        if (!defined('ERROR_REPORTING_NO_DEBUG')) {
            $calledIn = self::generateCallTrace(1);
            if (!is_string($message) && method_exists($message, 'getMessage')) {
                $message = $message->getMessage().' at '.$message->getFile().' on line '.$message->getLine();
            }
            self::log(($calledIn?$calledIn.' - ':'').'[debug] '.$message, $message_type, $destination, $extra_headers);
        }
    }

    public static function info($message, $message_type = null, $destination = null, $extra_headers = null) {
        if (!defined('ERROR_REPORTING_NO_INFO')) {
            $calledIn = self::generateCallTrace(1);
            if (!is_string($message) && method_exists($message, 'getMessage')) {
                $message = $message->getMessage().' at '.$message->getFile().' on line '.$message->getLine();
            }
            self::log(($calledIn?$calledIn.' - ':'').'[info] '.$message, $message_type, $destination, $extra_headers);
        }
    }

    public static function warning($message, $message_type = null, $destination = null, $extra_headers = null) {
        if (!defined('ERROR_REPORTING_NO_WARNING')) {
            $calledIn = self::generateCallTrace(1);
            if (!is_string($message) && method_exists($message, 'getMessage')) {
                $message = $message->getMessage().' at '.$message->getFile().' on line '.$message->getLine();
            }
            self::log(($calledIn?$calledIn.' - ':'').'[warning] '.$message, $message_type, $destination, $extra_headers);
        }
    }

    public static function generateCallTrace(int $index = -1): string
    {
        if (self::$DEBUG) {
            $trace = debug_backtrace();
            if ($index !== -1) {
                if (isset($trace[$index])) {
                    $trace = self::formatTrace($trace[$index]);
                }
                else {
                    foreach ($trace as $i=>$arg) {
                        $trace[$i] = self::formatTrace($trace[$i]);
                    }
                }
            }
            return json_encode($trace);
        }
        return "";
    }

    private static function formatTrace(array $trace) {
        $o = [];
        if (isset($trace['file'])) {$o[] = $trace['file'].(isset($trace['line'])?'('.$trace['line'].')':'');}
        if (isset($trace['class'])) {$o[] = $trace['class'].(isset($trace['function'])?'.'.$trace['function'].(isset($trace['args'])?'('.implode(', ', $trace['args']).')':''):'');}
        return implode(' - ', $o);
    }
}