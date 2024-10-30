<?php

namespace mFrame\Render;

use mFrame\Pattern\Singleton;

class Theme extends Singleton {

    protected static string $theme_directive = "Default";
    protected static string $theme_path;
    protected static array $partials;
    protected static array $parsed_view;
    protected static array $order = ["head", "menu", "content", "foot"];

    public static function run() : void {
        if(static::getDirective("Application", "theme")){
            static::$theme_directive = ucfirst( strtolower( static::getDirective("Application", "theme") ) );
        }

        if(is_dir(SKIN . static::$theme_directive . DIRECTORY_SEPARATOR )){
            static::$theme_path = SKIN . static::$theme_directive . DIRECTORY_SEPARATOR;
        }

        if(file_exists(static::$theme_path . "skeleton.json")){
            static::$order = json_decode(file_get_contents(static::$theme_path . "skeleton.json"), true);
        }
    }

    protected static function runPartials() : void {
        foreach( static::$order as $index => $file){
            if($index == "content"){
                $process = static::Partial(static::$parsed_view["file"], static::$parsed_view["action"], static::$parsed_view["data"]);
            } else {
                $theme_part = static::$theme_path . $index . ".php";
                $process = static::Partial($theme_part);
            }

            static::$partials[$index] = $process;
        }
    }

    protected static function Partial(string $file_or_content, string $action = "index", array $data = []) : bool | string {
        if(file_exists($file_or_content)){
            ob_start();
            include $file_or_content;
            $render = ob_get_contents();
            ob_end_clean();

            return $render;
        }

        return false;
    }

    protected static function render() : void {
        echo implode("\n", static::$partials);
        die();
    }

}