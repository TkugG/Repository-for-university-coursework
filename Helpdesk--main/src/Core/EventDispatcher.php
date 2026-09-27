<?php

namespace App\Core;

class EventDispatcher
{
    private static ?EventDispatcher $instance = null;
    private array $listeners = [];

    public static function getInstance(): EventDispatcher
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function listen(string $eventName, callable|array $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(string $eventName, array $payload = []): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            try {
                if (is_callable($listener)) {
                    call_user_func($listener, $payload);
                } elseif (is_array($listener) && count($listener) === 2) {
                    [$classOrObj, $method] = $listener;
                    if (is_string($classOrObj)) {
                        $obj = new $classOrObj();
                        $obj->$method($payload);
                    } else {
                        $classOrObj->$method($payload);
                    }
                }
            } catch (\Throwable $e) {
                error_log("EventDispatcher error on [{$eventName}]: " . $e->getMessage());
            }
        }
    }
}
