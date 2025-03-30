<?php

namespace Nurymbet\Core;

use Nurymbet\Core\Container\Container;
use Nurymbet\Core\Error\Handler;
use Nurymbet\Route\Auth\Route;

class Queue
{
    private static function ErrorInit(): void
    {
        Handler::init();
    }

    private static function ContainerInit(): void
    {
        Container::load();
    }

    private static function RouteInit(): void
    {
        Route::save();
    }

    public static function init(): void
    {
        static::ErrorInit();
        static::ContainerInit();
        static::RouteInit();
    }
}
