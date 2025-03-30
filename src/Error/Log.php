<?php

namespace Nurymbet\Core\Error;

use Symfony\Component\VarDumper\VarDumper;

class Log
{
    private static ?Config $config = null;

    private static function init(): void
    {
        if (!static::$config) {
            static::$config = Config::getInstance();
        }
    }

    private static function message(string $type, string $message, string $file, int $line): string
    {
        static::init();
        return str_replace(
            [
                '{type}',
                '{message}',
                '{file}',
                '{line}',
                '{date}'
            ],
            [
                $type,
                $message,
                $file,
                $line,
                static::date()
            ],
            static::$config->get('message_format')
        );
    }

    public static function write(string $type, string $message, string $file, int $line): void
    {
        static::init();
        $message = self::message($type, $message, $file, $line);
        file_put_contents(static::$config->get('log_file'), $message . "\n", FILE_APPEND);

        $colorsList = static::$config->get('colors');
        $color = $colorsList['green'];

        if ($type === 'Fatal Error') {
            $color = $colorsList['red'];
        } elseif (in_array($type, ['Warning', 'Notice'], true)) {
            $color = $colorsList['yellow'];
        } elseif ($type === 'Exception' || $type === 'Throwable') {
            $color = $colorsList['blue'];
        }

        echo $color . $message . $colorsList['reset'] . "\n";
    }

    private static function date(): string
    {
        static::init();
        return date(static::$config->get('date_format'));
    }
}
