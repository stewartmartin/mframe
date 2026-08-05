<?php

namespace mSkel\App\Controllers;

use mFrame\Axis\Controller;

class Error extends Controller {

    public function unauthorized(){
        return $this->standardReturn("error", [], "unauthorized");
    }

    public function restricted(){
        return $this->standardReturn("error", [], "unauthorized");
    }

    public function notfound(){
        return $this->standardReturn("error", [], "notfound");
    }

    public function internal(){
        return $this->standardReturn("error", [], "internal");
    }

    public function index(): mixed {
        return $this->standardReturn("error", [], "index");
    }

    protected function setRequirements(): void {

    }

    protected function loadRequirements(): void {}
}