<?php

namespace mFrame\Axis;

use mFrame\Pattern\Factory;

abstract class Controller extends Factory {

    public array $requiredModels = [];
    public array $requiredHelpers = [];
    public array $requiredMiddleware = [];
    public bool $isApi = false;

    public function run() : void {
        if(empty($this->requiredModels) || empty($this->requiredHelpers) || empty($this->requiredMiddleware)){
            if(method_exists($this, "setRequirements")){
                $this->setRequirements();
            }
        }

        $this->loadRequirements();
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
            $requires["Middleware"] = $this->requiredMiddleware;
        }

        if(empty($requires)){
            return;
        }

        foreach($requires as $pointer => $required){
            $path = "Structure_App_" . ucfirst(strtolower($pointer));
            if(!defined($path)){
                $target = ucfirst(strtolower($pointer));
                if(is_dir(ROOT . "App" . DIRECTORY_SEPARATOR . $target)){
                    define($path, ROOT . "App" . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR);
                }
            }

            //This needs refactoring and sent back to the framework.
            foreach($required as $property => $classFile){
                if(file_exists($path . ucfirst(strtolower($classFile)) . ".php")){
                    $class = extractName($path . ucfirst(strtolower($classFile)) . ".php", "n") . "\\" . extractName($path . ucfirst(strtolower($classFile)) . ".php", "c");
                    require_once($path . ucfirst(strtolower($classFile)) . ".php");
                    $this->push($property, new $class());
                }
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
