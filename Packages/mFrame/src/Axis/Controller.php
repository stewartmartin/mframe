<?php

namespace mFrame\Axis;

use mFrame\Pattern\Factory;

abstract class Controller extends Factory {

    public array $requiredModels = [];

    public function run(){
        if(empty($this->requiredModels)){
            if(method_exists($this, "setRequirements")){
                $this->setRequirements();
            }
        }
    }

    protected function loadRequirements() : void {
        foreach($this->requiredModels as $pointer => $classname){
            $abs_path = APP . "Models" . DIRECTORY_SEPARATOR . $classname . ".php";
            if(file_exists($abs_path)){
                if(!validate_file($abs_path)){
                    terminate("This is an invalid file");
                }

                require_once($abs_path);

                $classCaller = implode("\\", [extractName("NS", $abs_path), extractName("cs", $abs_path)]);

                $this->push($pointer, new $classCaller());
            } else {
                terminate("A required file is missing.");
            }

        }
    }

    protected function standardReturn(string $viewRequested, array $viewData = [], string $viewSwitch = "index") : array {
        return array(
            "viewFile" => $viewRequested,
            "switch" => $viewSwitch,
            "data" =>  $viewData,
        );
    }

    abstract function setRequirements() : void;
}