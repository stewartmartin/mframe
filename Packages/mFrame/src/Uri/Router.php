<?php

namespace mFrame\Uri;

use mFrame\Pattern\Singleton;
use mFrame\Uri\Request;

class Router extends Singleton {

    protected static array $routeCollection = [];
    protected static object $auth;

    public static function run(){
        if(array_key_exists("Auth", self::$rawConfig) && is_object(self::$rawConfig["Auth"])){
            self::$auth = self::$rawConfig["Auth"];
        } else {
            terminate("Auth not passed, or configured.");
        }

        static::$routeCollection["GET"] = array();
        static::$routeCollection["POST"] = array();
    }

    public static function Add(string $requestMethod, string $uriPattern, callable $callback, bool $restricted = false, string $restrictedGroup = null, bool $Override = false): void {
        if($restricted){
            self::$auth->setAuth();
            if(!self::$auth->checkStatus()){
                Request::redirect("/restricted");
            }

            if(!is_null($restrictedGroup)){
                $groups = self::$auth->buildUser();
                if(!empty($groups["groups"])){
                    $arr = $groups["groups"]->pull("group_name");
                    if(is_int($restrictedGroup)){
                        $arr = $groups["groups"]->pull("group_id");
                    }
                    if(!in_array($restrictedGroup, $arr)){
                        Request::redirect("/unauthorized");
                    }
                }
            }
        }

        if($Override){
            static::$routeCollection[strtoupper($requestMethod)] = [strtolower($uriPattern) => $callback];
        }

        $method = strtoupper($requestMethod);
        if(in_array($method, static::$routeCollection)){
            static::$routeCollection[strtoupper($requestMethod)] = array(strtolower($uriPattern) => $callback);
        } else {
            terminate("Invalid Request Method. Hacking attempt detected.");
        }

    }

    public static function Dispatch() : bool | Array {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if(in_array($method, static::$routeCollection)){
            $method_routes = array_keys(static::$routeCollection[$method]);
            $userRequest = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            foreach($method_routes as $route){
                if(preg_match($route, $userRequest)){
                    return call_user_func(static::$routeCollection[$method][$route]);
                }
            }
        }
        return false;
    }

    public static function BuildRoute(mixed $path){
        if(is_array($path)){
            $path = implode("/", $path);
        }

        static::getDirective("application", "domain_name") . "/" . $path;
    }

    public static function RouteReturn(string $controller, string $method = "index", array $params = [], bool $restricted = false, string $restrictedGroup = null) : array {
        return array(
            "Controller" => $controller,
            "Method" => $method,
            "Params" => $params,
            "Restricted" => $restricted,
            "RestrictedGroup" => $restrictedGroup
        );
    }

}