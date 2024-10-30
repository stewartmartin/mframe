<?php

namespace mFrame\Uri;

use mFrame\Pattern\Singleton;

class Request extends Singleton {

    protected static bool $secure = true;
    protected static array $areas;
    protected static string $UriEntry;

    public static array $requested_area;

    public static function run(){
        if(emtpy(static::$areas)){
            static::$areas = static::getDirective("Application", "routing");
        }

        if(empty(static::$UriEntry)){
            static::$UriEntry = static::getDirective("Application", "BaseURL");
        }

        if(static::$UriEntry !== parse_url(static::$UriEntry, PHP_URL_HOST)){
            terminate("Invalid host used to access this application. Terminating operation");
        }
    }

    protected static function setArea() : bool {
        if(!empty(static::$areas)){
            foreach(static::$areas as $area_key => $area_directives){
                $target = $_SERVER["REQUEST_URI"];
                if(str_contains($_SERVER["REQUEST_URI"], rtrim("/", $target["path"]))){
                    static::$requested_area = static::$areas[$area_key];
                    if(empty(static::$requested_area["routes_file"])){
                        static::$requested_area["routes_file"] = ucfirst( strtolower( $area_key) ) . ".php";
                    }
                    break;
                }
            }

            if(empty(static::$requested_area)){
                static::$requested_area = static::$areas["site"];
            }
        }

        return true;
    }

    public static function pullBase() : string {
        $protocol = "http";
        if(static::$secure){
            $protocol .= "s";
        }

        return $protocol . "://" . static::$UriEntry . "/";
    }

    public static function buildUri(array $uri = null) : string {
        $base = static::pullBase();

        if(is_array($uri)){
            $uri = implode("/", $uri);
        }

        return $base . $uri;
    }

    public static function redirect(array $uri, int $code = 302 ) : void {
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