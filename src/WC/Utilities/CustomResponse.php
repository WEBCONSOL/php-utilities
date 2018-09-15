<?php

namespace WC\Utilities;

class CustomResponse
{
    private static $debug = false;

    public static function setDebug(bool $debug) {self::$debug = $debug;}

    public static function getDebug(): bool {return self::$debug;}

    public static function render(int $code, $msg=null, bool $status=true, array $data=array()): string {
        header('Content-Type: application/json');
        http_response_code($code);
        die(self::getErrorOutput($code, $msg, $status, $data));
    }

    public static function getErrorOutput(int $code, $msg=null, bool $status=true, array $data=array()): string {
        return json_encode(self::getErrorOutputAsArray($code, $msg, $status, $data));
    }

    public static function getErrorOutputAsArray(int $code, $msg=null, bool $status=true, array $data=array()): array {
        $output = array();
        $output['status'] = $code === 200;
        $output['code'] = $code;
        if ($msg) {
            $output['message'] = $msg;
        }
        else if ($code === 200) {
            $output['message'] = 'Success';
        }
        else if ($code === 400) {
            $output['message'] = 'Bad request';
        }
        else if ($code === 403) {
            $output['message'] = 'Forbidden to access this resource.';
        }
        else if ($code === 404) {
            $output['message'] = 'Resource Not Found. We cannot find the resource you requested.';
        }
        else {
            $output['message'] = 'Internal error.';
        }

        $output['data'] = sizeof($data) ? $data : null;
        if (self::$debug) {
            $output['debug'] = debug_backtrace();
        }
        return $output;
    }

    public static function renderJSONString(string $data) {
        header("Content-Type: application/json; charset=utf-8");
        //header('Content-Disposition','attachment;filename="'.uniqid('json-file-').'.json"');
        die($data);
    }

    public static function renderPlaintext(string $data) {
        header("Content-Type: text/html; charset=utf-8");
        die($data);
    }
}