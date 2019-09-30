<?php

namespace WC\Utilities;

class CustomErrorHandler
{
    public static function init(bool $overrideErrorHandler=true) {
        set_exception_handler("\WC\Utilities\CustomErrorHandler::exceptionHandler");
        if ($overrideErrorHandler) {
            set_error_handler("\WC\Utilities\CustomErrorHandler::errorHandler");
        }
    }

    public final static function errorHandler($errCode, $errStr, $errFile, $errLine, $errContext)
    {
        $message = 'Error: ' . $errStr . '; file: ' . $errFile . '; line: ' . $errLine;
        Logger::error($message);
        CustomResponse::render($errCode, $message);
    }

    public final static function exceptionHandler($e)
    {
        if ($e instanceof \Error || $e instanceof \Exception) {
            $message = 'Exception: ' . $e->getMessage() . '; file: ' . $e->getFile() . '; line: ' . $e->getLine();
            Logger::error($message);
            CustomResponse::render($e->getCode(), $message);
        }
    }
}