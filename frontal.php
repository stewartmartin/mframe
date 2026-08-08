<?php

namespace mSkel;

use mFrame\Uri\Router;
use mFrame\Uri\Request;
use mFrame\Render\Theme;

use mSkel\App\MiddleWare\Auth;

class frontal {

    protected Router $Router;
    protected Request $Request;

    public function __construct(){
        $this->Router = Router::initiate([]);
        $this->Request = Request::initiate([]);
        $this->setArea();
    }

    protected function setArea() : bool {
        if(!empty(Request::$requested_area)){
            if(Request::$requested_area["Restricted"]){
                $auth = new Auth();
                $auth->setAuth(true);
                define("Auth", $auth);
                if(!$auth->isAuthenticated()){
                    //Send them to login?
                    Request::redirect("/login");
                }
            }
        }

        if(defined("Structure_Directives_Routes")){
            $loaded = false;
            if(is_string(Request::$requested_area["routes_file"])){
                if(is_file(Structure_Directives_Routes . Request::$requested_area["routes_file"])){
                    require_once(Structure_Directives_Routes . Request::$requested_area["routes_file"]);
                    $loaded = true;
                } else{
                    terminate("Could not locate required routes file.");
                }
            }

            if(is_array(Request::$requested_area["routes"])){
                foreach(Request::$requested_area["routes"] as $route){
                    if(is_file(Structure_Directives_Routes . $route)){
                        require_once(Structure_Directives_Routes . $route);
                    } else {
                        terminate("Could not locate required routes file.");
                    }
                }
                $loaded = true;
            }

            if($loaded){
                $this->load_controller( $this->Router->dispatch() );
                return true;
            }
        }
        return false;
    }

    protected function load_controller(array $parse_request) : void {
        if(defined("Structure_App_Controllers")) {
            $controller_path = Structure_App_Controllers . ucfirst(strtolower($parse_request["controller"])) . ".php";
            if (file_exists($controller_path)) {
                $control = extractName($controller_path, "n") . "\\" . extractName($controller_path, "c");
                require_once($controller_path);

                $method = $parse_request["method"];
                $params = $parse_request["params"];

                $control = new $control();
                if (method_exists($control, $method)) {
                    $parsed_request = $control->$method($params);
                    if (is_array($parsed_request)) {
                        $this->render($parsed_request);
                    }
                }
                terminate("There was an issue processing the requested URI.");
            }
            terminate("We were not able to find the requested path.");
        }
        terminate("There are missing structure definitions.");
    }

    protected function render(array $parsed_request) : void {
        if (defined("Structure_App_Views")) {
            $file = Structure_App_Views . $parsed_request["viewFile"] . ".php";
            if (file_exists($file)) {
                $action = $parsed_request["switch"];
                $data = $parsed_request["data"];
                $theme_data = array(
                    "viewFile" => $file,
                    "viewAction" => $action,
                    "viewData" => $data,
                );

                $theme = Theme::initiate( $theme_data );
                $theme::render();
            }
        }


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
            "Public" => array(
                "ROOT" => "Public",
                "Skin" => "Skin",
            ),
            "bin" => "bin",
            "App" =>
                array(
                    "ROOT" => "App",
                    "Models" => "Models",
                    "Views" => "Views",
                    "Controllers" => "Controllers",
                    "Middleware" => "Middleware",
                ),
            "Directives" =>
                array(
                    "Config" => "Config",
                    "Routes" => "Routes",
                ),
            "Tasks" => "Tasks",
            "Seeds" => array(
                "ROOT" => "Seeds",
                "Database" => "Database",
                "MemCache" => "MemCache",
            ),
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