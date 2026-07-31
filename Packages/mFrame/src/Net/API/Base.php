<?php

namespace mFrame\Net\API;

use mFrame\Pattern\Singleton;

abstract class Base extends Singleton {

    private static array $headers = [];
    private static array $responseHeaders = [];
    private static array $responseHeadersList = [];
    private static string $responseStatusLine;
    private static string $responseBody;
    private static bool $failOnError = false;
    private static bool $followLocation = false;
    private static int $maxRedirects = 20;
    private static int $redirectsFollowed = 0;
    private static bool $lastError = false;
    private static bool $rawResponse = false;
    private static string $contentType;

    abstract public static function initConnection() : bool;

    public static function setBooleans() : void {
        foreach( ["failOnError", "followLocation", "lastError", "storeRawResponse" ] as $boolean ){
            $makeBoolean = false;
            if(array_key_exists($boolean, static::$rawConfig)){
                $makeBoolean = static::$rawConfig[$boolean];
                self::pushStatic($boolean, $makeBoolean);
            }
        }
    }

    public static function setContentType() : void {
        self::$contentType = "application/json";
        if(isset(static::$rawConfig["contentType"])){
            self::$contentType = static::$rawConfig["contentType"];
        } else if(isset(static::$rawConfig["mediaType"])){
            self::$contentType = static::$rawConfig["mediaType"];
        }
    }

    public static function run() : void {
        if(!empty(static::$rawConfig)){
            self::setContentType();
            if(static::validateStatic("initConnection")){
                static::initConnection();
            } else {
                terminate("The initation of the connection is not defined within the class.");
            }
        }
    }

}