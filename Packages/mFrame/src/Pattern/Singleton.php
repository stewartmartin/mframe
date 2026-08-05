<?php

namespace mFrame\Pattern;

use mFrame\Base\{Configuration, Mutator};

abstract class Singleton {

    use Configuration, Mutator;

    private static array $instance;

    private function __construct(mixed $config = "") {
        if(!empty($config)){
            if(is_array($config)){
                self::$rawConfig = $config;
                self::processConfigs();
            } else {
                foreach ($config as $directive => $value) {
                    if (self::validateStatic($directive)) {
                        self::pushStatic($directive, $value);
                    }
                }
            }
        }

    }
    private function __clone() {}
    private function __wakeup() {}

    public static function initiate(Mixed $params = "") : object {
        $caller = get_called_class();
        if(!in_array($caller, static::$instance)) {
            static::$instance[$caller] = new static($params);
        }

        if(method_exists(static::$instance[$caller], "run")){
            static::$instance[$caller]::run();
        }

        return static::$instance[$caller];
    }

    /*
     * The Run method is called to supplement any additional setup that needs to be done previous to configuration params
     * being passed and set by the initiate function.
     *
     * As this is an abstract class the fun method is required for any class that extends this Singleton.
     */
    abstract public static function run();
    protected static function loadDirectives(string $container = "", string $directive = "", string $subDirective = "" ) : bool {
        if(!empty($container)){
            if(!empty($directive)){
                if(!empty($subDirective)){
                    return self::getConfig($directive, $subDirective, $container);
                }
                return self::getConfig($directive, $container);
            }
            return self::getConfig($container);
        }
        return false;
    }
}