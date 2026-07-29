<?php

namespace mFrame\Axis;

use mFrame\Pattern\Factory;

abstract class Controller extends Factory {

    public array $requiredModels = [];
    public bool $isApi = false;

    public function run(){
        if(empty($this->requiredModels)){
            if(method_exists($this, "setRequirements")){
                $this->setRequirements();
            }
        }
    }

    protected function loadRequirements() : void {
        $requires = [];
        if(!empty($this->requiredModels)){
            $requires["Models"] = $this->requiredModels;
        }

        if(!empty($this->requiredHelpers)){
            $requires["Helpers"] = $this->requiredHelpers;
        }

        if(!empty($this->requiredMiddelwares)){
            $requires["Middlewares"] = $this->requiredMiddlewares;
        }

        if($this->protected && !array_key_exists("Authentication", $requires["Middlewares"])){
            $requires["Middlewares"]["Authentication"] = "app\\middlewares\\Auth";
        }

        foreach($requires as $pointer => $required){
            $path = "STRUCTURE_APP_" . strtoupper($pointer);
            if(!defined($path)){
                if(is_dir(ROOT . "App" . DIRECTORY_SEPARATOR . strtolower($pointer))){
                    define($path, ROOT . "App" . DIRECTORY_SEPARATOR . strtolower($pointer) . DIRECTORY_SEPARATOR);
                }
            }

            foreach($required as $variable => $fullClassName){
                if(!class_exists($fullClassName)){
                    if(file_exists($$path . $fullClassName . ".php")){
                        require_once($$path . $fullClassName . ".php");
                    }

                    terminate("The class " . $fullClassName . " does not exist.");
                }

                $this->push($variable, new $fullClassName());
            }
        }
    }

    protected function standardReturn(string $viewRequested = "", array $viewData = [], string $viewSwitch = "index") : array {
        if(empty($viewRequested)){
            $viewRequested = "pages";
        }

        if($this->isApi || ( strtolower($viewRequested) == "api") ){
            renderApi($viewData);
        }
        
        return array(
            "viewFile" => $viewRequested,
            "switch" => $viewSwitch,
            "data" =>  $viewData,
        );
    }

    public function dynamics(string $dynamic, array | object $params) : array {
        if (method_exists($this, $dynamic)) {
            return $this->$dynamic($params);
        }

        return $this->renderError("404");
    }

    public function renderError(string | int $errorCode) : array {
        return $this->standardReturn("errors.php", debug_backtrace(), $errorCode );
    }

    abstract protected function setRequirements() : void;
    abstract public function index() : mixed;
}
