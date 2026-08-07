<?php

namespace mFrame\Uri;

use mFrame\Pattern\Singleton;

class Request extends Singleton {

    protected static bool $secure = true;
    protected static array $areas;
    protected static string $method;
    protected static string $UriEntry;

    public static array $requested_area;

    public static function run() : void {
        if(empty(static::$areas)){
            $directives = static::getDirective("Application", "routing");
            if(array_key_exists("method", $directives)){
                static::$method = $directives["method"];
                if(array_key_exists("directives", $directives)){
                    static::$areas = $directives["directives"];
                }
            }
        }

        if(self::getDirective("Application", "domain_schema") == "http:"){
            static::$secure = false;
        }

        static::$UriEntry = static::getDirective("Application", "domain_name");
        static::setArea();
    }

    private static function currentRequest() : mixed {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $current_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        return parse_url($current_url);
    }

    protected static function setArea() : bool {
        $current_url = static::currentRequest();

        if($current_url["host"] == static::$UriEntry){
            if(static::$method == "port" && ($current_url["port"] !== 80 && $current_url["port"] !== 443)){
                $entry = $current_url["port"];
            } elseif(static::$method == "subdomain"){
                //Might need to refactor this at a later date to include multiple subdomains.
                $entry = explode(".", $current_url["host"])[0];
            } else {
                $path_parts = explode("/", ltrim($current_url["path"], "/"));
                if(empty($path_parts[0])){
                    $entry = "/";
                } else {
                    $entry = $path_parts[0];
                }
            }

            //Now that we have the method we can run through the directives.
            foreach(static::$areas as $area_key => $area_directives){
                if($area_directives["path"] == $entry){
                    static::$requested_area = static::$areas[$area_key];
                    return true;
                }
            }
            terminate("Invalid entry token.");
        }
    }

    public static function pullBase() : string {
        $protocol = "http";
        if(static::$secure){
            $protocol .= "s";
        }

        return $protocol . "://" . static::$UriEntry . "/";
    }

    public static function buildUri(array  | string $uri = null) : string {
        $base = static::pullBase() . "/";

        if(is_array($uri)){
            $uri = implode("/", $uri);
        }

        if(is_null($uri)){
            $uri = "";
        }

        return $base . $uri;
    }

    public static function redirect(array | string $uri, int $code = 302 ) : void {
        header("Location: " . static::buildUri($uri), true, $code);
    }

    public static function SanitizePost(array $post) : bool | array {
        if(!empty($_POST)){
            $sanitized = array();
            foreach($_POST as $post_key => $post_value){
                $sanitized[$post_key] = filter_var($post_value, FILTER_DEFAULT);
            }

            return $sanitized;
        }
        return false;
    }

    public static function extractHeaders(array $headerMap = [], string $method = "post") : bool | array {
        if(!empty($headerMap)){
            if($method == "post"){
                $processed = array();
                foreach($headerMap as $header){
                    $target = $header[0];
                    $newTarget = $header[1];
                    if(in_array($target, $_POST)){
                        $processed[$newTarget] = $_POST[$target];
                    } else {
                        continue;
                    }
                }

                if(count($processed) == count($headerMap)){
                    return $processed;
                }
            }
        }

        return false;
    }
}