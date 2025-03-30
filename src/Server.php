<?php

namespace Nurymbet\Core;

use Nurymbet\Core\Container\App;
use Nurymbet\Core\Error\Log;
use Nurymbet\Route\Core\Flux;
use Psr\Http\Message\ServerRequestInterface;
use Quark\Common\Connect;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;

class Server
{
    private static array $config = [];

    public static function config(
        string $host = '127.0.0.1',
        int $port = 5000,
        string $timezone = 'Europe/Moscow',
        int $workers = 4,
        int $max_request_size = 10 * 1024 * 1024,
        int $keep_alive_timeout = 60,
        bool $enable_compression = true,
        array $static_files = ['public/assets'],
        string $log_level = 'debug',
        bool $enable_caching = true,
        int $cache_max_age = 3600,
        array $security_headers = []
    ): static {
        static::$config = compact(
            'host',
            'port',
            'timezone',
            'workers',
            'max_request_size',
            'keep_alive_timeout',
            'enable_compression',
            'static_files',
            'log_level',
            'enable_caching',
            'cache_max_age',
            'security_headers'
        );

        date_default_timezone_set($timezone);
        return new static();
    }

    public static function create(): void
    {
        Queue::init();

        $server = new HttpServer(function (ServerRequestInterface $request): Response {
            try {
                $path = $request->getUri()->getPath();
                if ($path === '/favicon.ico') {
                    return new Response(404, [], 'Not Found');
                }

                $connect = new Connect('react_postgres', 5432, 'app', 'user', 'password');
                $connect->connect();

                foreach (static::$config['static_files'] as $static_dir) {
                    $staticPath = rtrim($static_dir, '/') . str_replace('/assets', '', $path);

                    if (str_starts_with($path, '/assets') && file_exists($staticPath) && is_file($staticPath)) {
                        $mime = mime_content_type($staticPath);

                        $headers = ['Content-Type' => $mime];

                        if (static::$config['enable_caching']) {
                            $headers['Cache-Control'] = 'public, max-age=' . static::$config['cache_max_age'];
                        }

                        foreach (static::$config['security_headers'] as $key => $value) {
                            $headers[$key] = $value;
                        }

                        return new Response(200, $headers, file_get_contents($staticPath));
                    }
                }


                RequestConfig($request);
                return App::route(Flux::init())::run();
            } catch (\Throwable $e) {
                Log::write('Exception', $e->getMessage(), $e->getFile(), $e->getLine());
                return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                    'error' => 'Internal Server Error',
                    'message' => static::$config['log_level'] === 'debug' ? $e->getMessage() : null,
                ]));
            }
        });

        $socket = new SocketServer(static::$config['host'] . ':' . static::$config['port']);

        echo "✅ Server running at http://" . static::$config['host'] . ':' . static::$config['port'] . PHP_EOL;
        echo "📂 Serving static files from: " . implode(', ', static::$config['static_files']) . PHP_EOL;
        $server->listen($socket);
    }
}
