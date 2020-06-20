<?php

namespace WC\Utilities;

class CustomResponse
{
    private static $debug = false;
    private static $debugInfo = null;

    public static function setDebug(bool $debug) {self::$debug = $debug;}
    public static function setDebugInfo($debug) {self::$debugInfo = $debug;}
    public static function getDebugInfo() {return self::$debugInfo;}

    public static function getDebug(): bool {return (self::$debug || (defined('DEBUG') && DEBUG === true));}

    public static function render(int $code, $msg=null, bool $status=true, array $data=array()): string {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        die(self::getOutputFormattedAsString($data, $code, $msg));
    }

    public static function getOutputFormattedAsArray(array $data=null, int $code=200, $msg=null, bool $status=true): array {
        $file = __DIR__ . '/data/' . $code . '.json';
        if (file_exists($file)) {
            $output = json_decode(file_get_contents($file), true);
        }
        else {
            $output = json_decode(file_get_contents(__DIR__ . '/data/500.json'), true);
        }
        $output['status'] = $code === 200 ? 'OK' : 'ERROR';
        $output['code'] = $code;
        if ($msg) {
            $output['message'] = $msg;
        }
        $output['data'] = !empty($data) ? $data : (self::getDebug()?[]:null);
        if (self::getDebug()) {
            $output['data']['debug'] = [];
            if (self::$debugInfo !== null) {
                $output['data']['custom_debug'] = self::$debugInfo;
            }
            $output['data']['debug_backtrace'] = self::debugBacktrace();
        }
        return $output;
    }

    public static function getOutputFormattedAsString(array $data=null, int $code=200, $msg=null): string{
        return json_encode(self::getOutputFormattedAsArray($data, $code, $msg));
    }

    public static function renderJSONString(string $data) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        //header('Content-Disposition','attachment;filename="'.uniqid('json-file-').'.json"');
        die(json_encode($data));
    }

    public static function renderPlaintext(string $data) {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(500);
        die($data);
    }

    public static function debugBacktrace(): array
    {
        $d1 = debug_backtrace();
        $d2 = [];
        foreach($d1 as $i=>$t) {
            $d2[$i] = [];
            if (isset($t['file'])) {$d2[$i]['file'] = $t['file'];}
            if (isset($t['line'])) {$d2[$i]['line'] = $t['line'];}
            if (isset($t['function'])) {$d2[$i]['function'] = $t['function'];}
            if (isset($t['class'])) {$d2[$i]['class'] = $t['class'];}
            //if (isset($t['object'])) {$d2[$i]['object'] = $t['object'];}
            //if (isset($t['type'])) {$d2[$i]['type'] = $t['type'];}
        }
        return $d2;
    }
}