<?php

namespace mFrame\Base;

use mFrame\Base\Mutator;

trait Configuration {

    use Mutator;

    protected static $rawConfig;
    protected static $container = "Config";

    public static function processConfigs() : bool {
        if(!empty(static::$rawConfig)) {
            foreach(array_keys(static::$rawConfig) as $key) {
                if(static::validateStatic($key)){
                    static::pushStatic($key, static::$rawConfig[$key]);
                }
            }
            return true;
        }
        return false;
    }

    public static function getConfig(string $container, string $directive, string $sub_directive = "", bool $isPackage = false ) : mixed {
        //if isPackage is set to true, pkgDIRECTIVE must be set as a global.
        if($isPackage){
            if(defined("pkgDIRECTIVE")){
                $container = pkgDIRECTIVE . DIRECTORY_SEPARATOR;
            }
        }
        if(!defined("DIRECTIVES") && defined("ROOT")){
            define("DIRECTIVES", ROOT . "Directies" . DIRECTORY_SEPARATOR);
        }

        if(is_dir(DIRECTIVES . $container . DIRECTORY_SEPARATOR)) {
            $container = DIRECTIVES . $container . DIRECTORY_SEPARATOR;
        } else {

        }
    }

    public static function getDirective( string $directive, mixed $sub_directive = null, mixed $sub_container = null) : mixed {
        if(defined("DIRECTIVES")){
            $container = DIRECTIVES;

            if(!is_null($sub_directive)){
                if(is_dir($container . $sub_directive)){
                    static::$container = $sub_directive;
                }
            }

            $container = $container . static::$container;

            if(file_exists($container . $directive . ".php")){
                $target = $container . $directive . ".php";
                if(validate_file($target)){
                    $data = include($target);
                    if(!is_null($sub_directive) && array_key_exists($sub_directive, $data)){
                        $data = $data[$sub_directive];
                    }

                    return $data;
                }
            }
        }

        return false;
    }

}