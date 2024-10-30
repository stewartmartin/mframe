<?php

namespace mFrame\Pattern;

use mFrame\Base\{Configuration, Mutator};

abstract class Singleton {

    use Configuration, Mutator;

    private static array $instance;

    private function __construct($config) {
        if(is_object($config)){
            $config = json_decode(json_encode($config), true);
        }

        static::$rawConfig = $config;

        if(!empty(static::$rawConfig)){
            static::processConfigs();
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

}