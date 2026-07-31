<?php

namespace mFrame\Net\API;

use mFrame\Pattern\Singleton;

class Rest extends Singleton {

    private static mixed $curl;
    protected static array $headers;
    protected static array $responseHeaders;
    protected static array $responseHeadersList;
    protected static mixed $responseBody;
    protected static string $responseStatusLine;
    protected static false $lastError;

    public static function run() : void {
        self::$contentType = "";
        if(empty(static::$curl)){
            static::initCurl();
        }
    }

    protected static function initCurl() : void {
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }

        static::$curl = curl_init();
        curl_setopt(static::$curl, CURLOPT_HEADERFUNCTION, [self::class, 'parseHeader']);
        curl_setopt(static::$curl, CURLOPT_WRITEFUNCTION, [self::class, 'parseBody']);
        curl_setopt(static::$curl, CURLOPT_USERAGENT, 'merzIT - mFrame BigCommerce API Client');
        curl_setopt(static::$curl, CURLOPT_ENCODING, '');

        $auth = strtolower(static::$headers["authMethod"]);
        if($auth === "basic"){
            self::useBasicAuth(static::$headers["username"], static::$headers["password"]);
        } else if($auth === "oauth"){
            self::useOAuth(static::$headers["clientID"], static::$headers["clientToken"]);
        } else {
            terminate("No valid authentication method supplied.");
        }


        $follow = false;
        if (!ini_get("open_basedir")) {
            $follow = true;
        }
        curl_setopt(static::$curl, CURLOPT_FOLLOWLOCATION, $follow);

        $timeout = 60;
        if(isset(static::$headers["timeout"])){
            $timeout = static::$headers["timeout"];
        }
        self::setTimeout($timeout);
    }

    public static function addHeader(string $key, string $value) : void {
        static::$headers[$key] = "$key: " . $value;
    }

    public static function removeHeader(string $key) : void {
        unset(static::$headers[$key]);
    }

    public static function useBasicAuth(string $username, string $password) : void {
        curl_setopt(self::$curl, CURLOPT_USERPWD, "$username:$password");
    }

    public static function useOAuth(string $clientID, string $clientToken) : void {
        self::addHeader('X-Auth-Client', $clientID);
        self::addHeader('X-Auth-Token', $clientToken);
    }

    public static function verifyPeer(bool $option = true)  : void {
        curl_setopt(self::$curl, CURLOPT_SSL_VERIFYPEER, $option);
    }

    public static function setTimeout(int $seconds)  : void {
        curl_setopt(self::$curl, CURLOPT_TIMEOUT, $seconds);
    }

    public static function parseHeader(mixed $curl, string $headers) : mixed {
        $parts = explode(': ', $headers);
        if(isset($parts[1])){
            $key = strtolower(trim($parts[0]));
            self::$responseHeadersList[$key][] = trim($parts[1]);
            self::$responseHeaders[$parts[0]] = trim($parts[1]);
        }

        return strlen($headers);
    }

    public static function parseBody(mixed $curl, string $body) : mixed {
        static::$responseBody .= $body;
        return strlen($body);
    }

    public static function getContentType() : string {
        $type = "application/json";
        if(isset(static::$rawConfig["contentType"])){
            $type = static::$rawConfig["contentType"];
        }
        return $type;
    }

    public static function initiateRequest() : mixed {
        self::$responseBody = '';
        self::$responseHeaders = [];
        self::$responseHeadersList = [];
        self::$responseStatusLine = '';
        self::$lastError = false;
        self::addHeader('Accept', self::getContentType());

        curl_setopt(self::$curl, CURLOPT_POST, false);
        curl_setopt(self::$curl, CURLOPT_PUT, false);
        curl_setopt(self::$curl, CURLOPT_HTTPGET, false);

        curl_setopt(self::$curl, CURLOPT_HTTPHEADER, self::$headers);
    }

}