<?php

namespace Nurymbet\Core\Container;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class Executor
{
    private array $instances = [];
    private array $singletons = [];
    private string $class;
    private string $method;
    private array $params;

    public function __construct(string $class, string $method, array $params = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->params = $params;
    }

    public function call(): mixed
    {
        return $this->execute();
    }

    public function setInstance(string $class, object $instance): void
    {
        $this->singletons[$class] = $instance;
    }

    public function make(string $class): object
    {
        if (isset($this->singletons[$class])) {
            return $this->singletons[$class];
        }

        if (isset($this->instances[$class])) {
            return call_user_func($this->instances[$class], $this);
        }

        return $this->resolveClassInstance($class);
    }

    private function execute(): mixed
    {
        if (!$this->exists()) {
            throw new Exception("Метод {$this->method} в классе {$this->class} не найден");
        }

        $instance = $this->resolveClassInstance($this->class);
        $reflection = new ReflectionMethod($instance, $this->method);
        $methodParams = $this->resolveMethodParams($reflection);

        return $reflection->invokeArgs($instance, $methodParams);
    }

    private function exists(): bool
    {
        return class_exists($this->class) && method_exists($this->class, $this->method);
    }

    private function resolveClassInstance(string $class): object
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $constructorParams = $this->resolveMethodParams($constructor);
        return $reflection->newInstanceArgs($constructorParams);
    }

    private function resolveMethodParams($reflection): array
    {
        $methodParams = [];

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if ($paramType instanceof ReflectionNamedType) {
                $paramTypeName = $paramType->getName();

                if (class_exists($paramTypeName)) {
                    $methodParams[$paramName] = $this->make($paramTypeName);
                    continue;
                }

                $methodParams[$paramName] = $this->resolvePrimitiveType($paramTypeName, $paramName);
            } else {
                $methodParams[$paramName] = $this->params[$paramName] ?? null;
            }
        }

        return $methodParams;
    }

    private function resolvePrimitiveType(string $type, string $paramName): mixed
    {
        return match ($type) {
            'int' => (int)($this->params[$paramName] ?? 0),
            'float' => (float)($this->params[$paramName] ?? 0.0),
            'bool' => (bool)($this->params[$paramName] ?? false),
            'string' => (string)($this->params[$paramName] ?? ''),
            'array' => (array)($this->params[$paramName] ?? []),
            default => null
        };
    }
}
