<?php

namespace Nurymbet\Core\Container;

use Exception;
use React\Http\Message\Response;

class Middleware
{
    public static function start(array $middlewares, array $params): ?Response
    {
        return static::flux($middlewares, $params);
    }

    private static function flux(array $middlewares, array $params): ?Response
    {
        foreach ($middlewares as $middleware) {
            if (isset($middleware[0], $middleware[1])) {
                $result = (new Executor($middleware[0], $middleware[1], $params))->call();

                if ($result instanceof Response) {
                    return $result;
                } elseif ($result === 'next') {
                    continue;
                } else {
                    throw new Exception("Middleware {$middleware[0]}@{$middleware[1]} must return a Response or 'next', but it returned nothing");
                }
            }
        }

        return null;
    }
}
