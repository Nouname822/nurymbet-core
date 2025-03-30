<?php

namespace Nurymbet\Core\Error;

use ErrorException;

class Handler
{
    private static function errorHandler(): void
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            $type = match ($severity) {
                E_WARNING          => 'Warning',
                E_NOTICE           => 'Notice',
                E_USER_WARNING     => 'User Warning',
                E_USER_NOTICE      => 'User Notice',
                E_DEPRECATED       => 'Deprecated',
                E_USER_DEPRECATED  => 'User Deprecated',
                default            => 'Error'
            };
            Log::write($type, $message, $file, $line);
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    private static function exceptionHandler(): void
    {
        set_exception_handler(function ($exception) {
            Log::write('Exception', $exception->getMessage(), $exception->getFile(), $exception->getLine());
        });
    }

    private static function shutdownHandler(): void
    {
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null) {
                $type = match ($error['type']) {
                    E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR => 'Fatal Error',
                    default => 'Unknown Fatal Error'
                };
                Log::write($type, $error['message'], $error['file'], $error['line']);
            }
        });
    }

    public static function init(): void
    {
        static::errorHandler();
        static::exceptionHandler();
        static::shutdownHandler();
    }
}
