<?php

namespace WC\Utilities;

class CustomErrorHandler
{
    private static $displayed = false;

    public static function init(bool $overrideErrorHandler=true) {
        set_exception_handler("\WC\Utilities\CustomErrorHandler::exceptionHandler");
        register_shutdown_function("\WC\Utilities\CustomErrorHandler::fatalErrorHandler");
        if ($overrideErrorHandler) {
            set_error_handler("\WC\Utilities\CustomErrorHandler::errorHandler");
        }
    }

    public final static function errorHandler($errCode, $errStr, $errFile, $errLine, $errContext)
    {
        if (!self::$displayed) {
            self::setDisplayed(true);
            $message = 'Error: ' . $errStr . '; file: ' . $errFile . '; line: ' . $errLine;
            Logger::error($message);
            CustomResponse::render($errCode, $message, false);
        }
    }

    public final static function exceptionHandler($e)
    {
        if (!self::$displayed) {
            self::setDisplayed(true);
            if ($e instanceof \Error || $e instanceof \Exception) {
                $message = 'Exception: ' . $e->getMessage() . '; file: ' . $e->getFile() . '; line: ' . $e->getLine();
                Logger::error($message);
                CustomResponse::render(500, $message, false);
            }
        }
    }

    public final static function fatalErrorHandler()
    {
        if (!self::$displayed) {
            self::setDisplayed(true);
            $message = '';
            $e = error_get_last();
            if (is_string($e)) {
                $message = strip_tags($e);
            }
            else if (isset($e['message'])) {
                $message = 'Exception: ' . $e['message'] . '; file: ' . $e['file'] . '; line: ' . $e['line'];
            }
            if ($message) {
                Logger::error($message);
                CustomResponse::render(500, $message, false);
            }
        }
    }

    public static final function setDisplayed(bool $displayed) {self::$displayed = $displayed;}
}