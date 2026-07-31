<?php

namespace mFrame\Net;

use mFrame\Pattern\Singleton;
use mFrame\Net\API\{Soap,Rest};

class Client extends Singleton {

    protected static mixed $Connection;

    public static function run(): void {
        if(!static::isConnected()){
            static::connect();
        }
    }

    public static function isConnected() : bool {
        return !empty(static::$Connection);
    }

    protected static function connect() : void {
        if(isset(static::$rawConfig["protocol"])){
            $protocol = "mFrame\\Net\\API\\" .  ucfirst(strtolower(static::$rawConfig["protocol"]));
            if(class_exists($protocol)){
                static::$Connection = $protocol::initiate(static::$rawConfig);
            }
        }
    }
}
