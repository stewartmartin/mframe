<?php

namespace mFrame\Base;

use mFrame\Base\Mutator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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

    public static function getConfig(string $container, string $directive, string $sub_directive = "", bool $isPackage = false ) : bool {
        //if isPackage is set to true, pkgDIRECTIVE must be set as a global.
        $config = false;
        if($isPackage){
            //The container must be the actual package name.
            if(defined("Sructure_Packages")){
                $basePath = Structure_Packages . $container . DIRECTORY_SEPARATOR;

                $RDI = new RecursiveDirectoryIterator($basePath);
                $RII = new RecursiveIteratorIterator($RDI);

                foreach($RII as $file){
                    if($file->isFile() && $file->getFileName == $directive){
                        $config = include($file->getPathname());
                        break;
                    }
                }
            } else {
                terminate("Invalid Package Global");
            }
        } else {
            if(defined("Structure_App_Config")){
                $basePath = Structure_Directives_Config;
                //Container can be a folder.
                if(is_dir($basePath . $container)){
                    //Directive MUST be the filename.
                    if(is_file($basePath . $container . DIRECTORY_SEPARATOR . $directive)){
                        $config = include($basePath . $container . DIRECTORY_SEPARATOR . $directive);
                    }
                } else if(is_file($basePath . $container)){
                    $config = include($basePath . $container);
                }
            }
        }

        if(is_array($config)){
            //Merge the config with rawConfig.
            self::$rawConfig = self::$rawConfig + $config;
            self::processConfigs();
            return true;
        }
        return false;
    }

    public static function getDirective( string $directive, string $sub_directive = null, string $sSub_container = null) : mixed {
        if(!empty(self::$rawConfig)){
            if(array_key_exists($directive, self::$rawConfig)){
                if(is_array(self::$rawConfig[$directive]) && array_key_exists($sub_directive, self::$rawConfig[$directive])){
                    if(array_key_exists($sSub_container, self::$rawConfig[$directive][$sub_directive])){
                        return self::$rawConfig[$directive][$sub_directive][$sSub_container];
                    }
                    return self::$rawConfig[$directive][$sub_directive];
                }
                return self::$rawConfig[$directive];
            }
        }
        return false;
    }

}