<?php

namespace WC\Utilities;

class CustomResponse
{
    //Ref: https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
    const HTTP_RESPONSE_CODES = [
        100,101,102,103,
        200,201,202,203,204,205,206,207,208,226,
        300,301,302,303,304,305,306,307,308,
        400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,421,422,423,424,425,426,428,429,431,451,
        500,501,502,503,504,505,506,507,508,510,511,
        103,218,419,420,430,450,498,499,509,526,529,530,598,
        444,494,495,496,497,499,
        520,521,522,523,624,525,526,527,530,
        460,463
    ];
    private static $debug = false;
    private static $debugInfo = null;

    public static function setDebug(bool $debug) {self::$debug = $debug;}
    public static function setDebugInfo($debug) {self::$debugInfo = $debug;}
    public static function getDebugInfo() {return self::$debugInfo;}

    public static function getDebug(): bool {return (self::$debug || (defined('DEBUG') && DEBUG === true));}

    public static function render(int $code, $msg=null, bool $status=true, array $data=array()): string
    {
        if (!in_array($code, CustomResponse::HTTP_RESPONSE_CODES)) {
            $code = 500;
        }

        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo self::getOutputFormattedAsString($data, $code, $msg, $status);
        exit(0);
    }

    public static function getOutputFormattedAsArray(array $data=null, int $code=200, $msg=null, bool $status=true): array
    {
        $file = __DIR__ . '/data/' . $code . '.json';
        if (file_exists($file)) {
            $output = json_decode(file_get_contents($file), true);
        }
        else {
            $output = json_decode(file_get_contents(__DIR__ . '/data/500.json'), true);
        }
        $output['status'] = $code === 200 || $status ? 'OK' : 'ERROR';
        $output['code'] = $code;
        if ($msg) {
            $output['message'] = $msg;
        }
        $output['data'] = !empty($data) ? $data : (self::getDebug()?[]:null);
        if (self::getDebug()) {
            if (self::$debugInfo !== null) {
                $output['data']['custom_debug'] = self::$debugInfo;
            }
            $output['data']['debug_backtrace'] = self::debugBacktrace();
        }
        return $output;
    }

    public static function getOutputFormattedAsString(array $data=null, int $code=200, $msg=null, $status=true): string
    {
        return json_encode(self::getOutputFormattedAsArray($data, $code, $msg, $status));
    }

    public static function renderJSONString(string $data)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        //header('Content-Disposition','attachment;filename="'.uniqid('json-file-').'.json"');
        echo json_encode($data);
        exit(0);
    }

    public static function renderPlaintext(string $data)
    {
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(500);
        echo json_encode($data);
        exit(0);
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