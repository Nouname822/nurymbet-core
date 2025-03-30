<?php

namespace Nurymbet\Core\Container;

use Exception;
use Nurymbet\Route\Http\JsonResponse;
use React\Http\Message\Response;
use Symfony\Component\VarDumper\VarDumper;

class App
{
    private static array $route = [];

    public static function route(array $route): static
    {
        static::$route = $route;
        return new static();
    }

    private static function checkRoute(array $route): bool
    {
        return !empty($route) &&
            isset(
                $route['handler'],
                $route['params'],
                $route['handler']['handler'],
                $route['handler']['handler'][0],
                $route['handler']['handler'][1],
                $route['handler']['path'],
                $route['handler']['name'],
                $route['handler']['method'],
            ) &&
            !empty($route['handler']['handler']);
    }

    public static function run(): Response
    {
        if (static::checkRoute(static::$route)) {
            if (!empty(static::$route['handler']['middleware'])) {
                $middleware = Middleware::start(static::$route['handler']['middleware'], static::$route['params']);

                if ($middleware instanceof Response) {
                    return $middleware;
                }
            }
            if (!empty(static::$route['handler']['options']['filters'])) {
                $filters = Middleware::start(static::$route['handler']['options']['filters'], static::$route['params']);

                if ($filters instanceof Response) {
                    return $filters;
                }
            }
            $handler = static::$route['handler']['handler'];
            $app = (new Executor($handler[0], $handler[1], static::$route['params']))->call();
            if ($app instanceof Response) {
                return $app;
            } else {
                throw new Exception("Метод {$handler[1]} в классе {$handler[0]} не вернул Response");
            }
        }
        return JsonResponse::send(404, ['message' => 'Страница не найдена']);
    }
}
