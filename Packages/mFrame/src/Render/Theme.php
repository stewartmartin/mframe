<?php

namespace mFrame\Render;

use mFrame\Pattern\Singleton;

class Theme extends Singleton {

    protected static string $theme_directive = "Default";
    protected static string $theme_path;
    protected static array $partials;
    protected static array $parsed_view;
    protected static array $order = ["head", "menu", "content", "foot"];

    public static bool $isApi = false;

    protected static array $viewData = [];
    protected static string $viewFile = "";
    protected static string $viewAction = "";

    public static function run() : void {
        if(static::$isApi){
            renderApi(static::$viewData,"200", "Success");
        }

        if(static::getDirective("Application", "Theme")){
            static::$theme_directive = ucfirst( strtolower( static::getDirective("Application", "Theme") ) );
        }

        if(defined("Structure_Public_Skin")){
            if(is_dir(Structure_Public_Skin . static::$theme_directive . DIRECTORY_SEPARATOR )){
                static::$theme_path = Structure_Public_Skin . static::$theme_directive . DIRECTORY_SEPARATOR;
                if(file_exists(static::$theme_path . "skeleton.json")){
                    static::$order = json_decode(file_get_contents(static::$theme_path . "skeleton.json"), true);
                }
            }
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
                $process = static::Partial(static::$viewFile, static::$viewAction, static::$viewData);
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

    protected static function render(string $partial = "") : void {
        self::runPartials();

        if(!empty($partial)){
            static::$partials["content"] .= $partial;
        }
        echo implode("\n", static::$partials);
        exit;
    }

}
