<?php

namespace mFrame\Authentication\Interfaces;

interface MethodInterface {

    public function authenticate() : bool;
    public function isAuthenticated() : bool;
    public function veto() : void;
    public function bySessionCookie() : bool;

}