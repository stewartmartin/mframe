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


}