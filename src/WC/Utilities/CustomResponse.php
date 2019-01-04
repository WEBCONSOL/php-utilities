<?php

namespace WC\Utilities;

class CustomResponse
{
    private static $debug = false;

    public static function setDebug(bool $debug) {self::$debug = $debug;}

    public static function getDebug(): bool {return self::$debug;}

    public static function render(int $code, $msg=null, bool $status=true, array $data=array()): string {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        die(self::getErrorOutput($code, $msg, $status, $data));
    }

    public static function getErrorOutput(int $code, $msg=null, bool $status=true, array $data=array()): string {
        return json_encode(self::getOutputFormattedAsArray($data, $code, $msg, $status));
    }

    public static function getOutputFormattedAsArray(array $data=null, int $code=200, $msg=null, bool $status=true): array {
        $file = __DIR__ . '/json/' . $code . '.json';
        if (file_exists($file)) {
            $output = json_decode(file_get_contents($file), true);
        }
        else {
            $output = json_decode(file_get_contents(__DIR__ . '/json/500.json'), true);
        }
        $output['status'] = $code === 200;
        $output['statusCode'] = $code;
        if ($msg) {
            $output['message'] = $msg;
        }

        $output['data'] = sizeof($data) ? $data : null;
        if (self::$debug) {
            $output['debug'] = debug_backtrace();
        }
        return $output;
    }

    public static function getOutputFormattedAsString(array $data=null, int $code=200, $msg=null): string{
        return json_encode(self::getOutputFormattedAsArray($data, $code, $msg));
    }

    public static function renderJSONString(string $data) {
        header('Content-Type: application/json; charset=utf-8');
        //header('Content-Disposition','attachment;filename="'.uniqid('json-file-').'.json"');
        die($data);
    }

    public static function renderPlaintext(string $data) {
        header('Content-Type: text/html; charset=utf-8');
        die($data);
    }
}