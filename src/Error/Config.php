<?php

/** ========================================
 *
 *
 *! Файл: Config.php
 ** Директория: alpha\src\Config.php
 *? Цель: Для удобной работы с конфигами для настройки обработчика ошибок используя Instance
 * Создано: 2025-03-29 08:34:14
 *
 *
============================================ */

namespace Nurymbet\Core\Error;

class Config
{
    private static ?self $instance = null;
    private array $config = [];

    private function __construct() {}

    /**
     * Для получение Instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Получение данных из конфига
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Для добавление/смены данных кэша
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $index => $k) {
            if ($index === array_key_last($keys)) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
    }

    /**
     * Получение всего конфига
     *
     * @return array
     */
    public function all(): array
    {
        return $this->config;
    }
}
