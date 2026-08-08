<?php

namespace mSkel\App\Controllers;

use mFrame\Axis\Controller;

class Login extends Controller {

    public function setRequirements(): void {
        $this->requiredModels = [];
        $this->requiredMiddleWare = [];
    }

    public function index() : array | false {

    }

    public function loadDirectives(string $container = "", string $directive = "", string $subDirective = ""): bool {}
}