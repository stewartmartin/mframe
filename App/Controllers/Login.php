<?php

namespace mSkel\App\Controllers;

use mFrame\Axis\Controller;

class Login extends Controller {

    public function setRequirements(): void {
        $this->requiredModels = [];
        $this->requiredMiddleWare = ["Auth" => "mSkel\\App\\MiddleWare\\Auth"];
    }

    public function index(){

    }

}