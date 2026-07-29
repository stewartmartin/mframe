<?php

namespace mSkel;

use mFrame\Uri\Router;
use mFrame\Uri\Request;
use mFrame\Render\Theme;

class frontal {

    protected Router $Router;
    protected Request $Request;

    public function __construct(){
        //Our directives should have been set and defined at this point. Lets make sure we have our packages.
        if(defined("PACKAGES")) {
            //The absolute first thing we need is our framework. So lets make sure its there and then grab that bitch.
            $abs2Frame = PACKAGES . "mFrame" . DIRECTORY_SEPARATOR;
            if (is_dir($abs2Frame) && file_exists($abs2Frame . "Entry.php")) {
                //Framework is the basis of the entire app/skeleton. It also sets foundations for additional custom packages.
                require_once($abs2Frame . "Entry.php");

                //From here we should determine our area.
                $this->setArea();
            }
        } else {
            terminate("Directives where not loaded/parsed correctly.");
        }
    }

    protected function setArea() : void {
        $this->Router = Router::initiate([]);
        $this->Request = Request::initiate([]);

        if(file_exists(DIRECTIVES . "Routing" . DIRECTORY_SEPARATOR . $this->Request::$requested_area["routes_file"])){
            require_once(DIRECTIVES . "Routing" . DIRECTORY_SEPARATOR . $this->Request::$requested_area["routes_file"]);
            $parse_request = $this->Router::Dispatch();
            if(is_array($parse_request)) {
                $this->load_controller($parse_request);
            }
        }

        terminate("Invalid area and or routes");
    }

    protected function load_controller(array $parse_request) : void {
        $controller_path = APP . "Controllers" . DIRECTORY_SEPARATOR . ucfirst( strtolower($parse_request["controller"]) ) . ".php";
        if(file_exists($controller_path)) {
            $control = extractName($controller_path, "n") . "\\" . extractName($controller_path, "c");
            require_once($controller_path);

            $method = $parse_request["method"];
            $params = $parse_request["params"];

            $control = new $control();
            if(method_exists($control, $method)) {
                $parsed_request = $control->$method($params);
                if(is_array($parsed_request)){
                    $this->render($parsed_request);
                }
            }
        }

        terminate("There was an error processing the user request.");
    }

    protected function render(array $parsed_request) : void {
        $file = APP . "Views" . DIRECTORY_SEPARATOR . $parsed_request["viewFile"] . ".php";
        if(file_exists($file)) {
            $action = $parsed_request["switch"];
            $data = $parsed_request["data"];
            $theme_data = array(
                "file" => $file,
                "action" => $action,
                "data" => $data,
            );

            $theme = Theme::initiate( array("parsed_view" => $theme_data) );
            $theme::render();
        }
    }

    public static function setStructure(array $customStructure = []) : bool {
        if(empty($customStructure)){
            $structure = array(
                "App" => "App",
                "Directives" => "Directives",
                "Packages" => "Packages",
                "Public" => "Public",
                "Seeds" => "Seeds",
                "Skin" => "Skin"
            );
        } else {
            $structure = $customStructure;
        }

        foreach($structure as $key => $directory){
            if(is_dir(ROOT . $directory . DIRECTORY_SEPARATOR)){
                define(strtoupper($key), ROOT . $directory . DIRECTORY_SEPARATOR);
            } else {
                terminate("Invalid directory passed fr structure.");
            }
        }

        return true;
    }

    /*
     * Here we're going to setup the static in order to load and get the application ready.
     */
    protected static array | object $RawConfig;

    public static function SysCheck() : bool {
        if(self::loadDirectives()){
            if(self::loadStructure()){
                $app_foundation = array("Controllers", "Middleware", "Models", "Views");
                foreach($app_foundation as $dir){
                    if(!defined("Structure_App_" . $dir)){
                        terminate("Application structure " . $dir . " is not defined.");
                    }

                    if(!is_dir(constant("Structure_App_" . $dir))){
                        terminate("Application structure " . $dir . " is not a directory.");
                    }
                }

                return self::startFramework();

            }
            terminate("We were not able to define the system directory structure.");
        }

        terminate("We are missing the important config folder or not able to access it.");
    }

    protected static function loadDirectives($type = "array") : bool {
        if(empty(self::$RawConfig)) {
            if (defined("Structure_Directives_Config")) {
                $items = realscan(Structure_Directives_Config);
                $directives = array();
                if (!empty($items)) {
                    foreach ($items as $item) {
                        $directives[str_replace(".php", "", $item)] = include(Structure_Directives_Config . $item);
                    }
                }

                if (!empty($directives)) {
                    return toDefine($directives, "directives");
                }
            }

            return false;
        }

        return true;
    }

    protected static function loadStructure(bool $returnStructure = false) : bool | array {
        $directories = array(
            "Public" => "Public",
            "bin" => "bin",
            "App" =>
                array(
                    "ROOT" => "App",
                    "Models" => "Models",
                    "Views" => "Views",
                    "Controllers" => "Controllers",
                    "Middlewares" => "Middlewares",
                ),
            "Directives" =>
                array(
                    "Config" => "Config",
                    "Routes" => "Routes",
                ),
            "Tasks" => "Tasks",
            "Database" => "Database",
            "Packages" => "Packages",
            "Storage" => array(
                "ROOT" => "Storage",
                "Cache" => "Cache",
                "Logs" => "Logs",
                "Sessions" => "Sessions"
            )
        );

        //we shouldn't need anything other than the out-of-pocket directories php file on the root folder.
        if( file_exists(ROOT . "Structure.php" ) ){
            $custom = include(ROOT . "Structure.php");
            if(!empty($custom)){
                $directories = $custom;
            }
        }

        if($returnStructure){
            return $directories;
        }

        return toDefine($directories, "structure");
    }

    protected static function startFramework() : bool {
        $framework = "mFrame";
        if(array_key_exists("framework", self::$RawConfig["App"])){
            $framework = self::$RawConfig["App"]["framework"];
        }

        $packageReady = false;
        if(file_exists(ROOT . $framework . DIRECTORY_SEPARATOR . "Entry.php")){
            require_once(ROOT . $framework . DIRECTORY_SEPARATOR . "Entry.php");
        }

        return $packageReady;
    }


}