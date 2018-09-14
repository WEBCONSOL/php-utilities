<?php

namespace WC\Utilities;

class CustomErrorHandler
{
    public static function init() {
        set_exception_handler("\WC\Utilities\CustomErrorHandler::exceptionHandler");
        set_error_handler("\WC\Utilities\CustomErrorHandler::errorHandler");
    }

    public final static function errorHandler($errCode, $errStr, $errFile, $errLine, $errContext)
    {
        $message = 'Error: ' . $errStr . '; file: ' . $errFile . '; line: ' . $errLine;
        CustomResponse::render($errCode, $message);
    }

    public final static function exceptionHandler($e)
    {
        if ($e instanceof \Error || $e instanceof \Exception) {
            $message = 'Exception: ' . $e->getMessage() . '; file: ' . $e->getFile() . '; line: ' . $e->getLine();
            CustomResponse::render($e->getCode(), $message);
        }
    }
}