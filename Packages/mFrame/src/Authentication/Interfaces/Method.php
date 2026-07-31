<?php

namespace mFrame\Authentication\Interfaces;

use mFrame\Authentication\Helpers\Database as DBHelper;
use mFrame\Authentication\Helpers\Session;
use mFrame\Authentication\Helpers\Cookie;
use mFrame\Authentication\Interfaces\MethodInterface;

use mFrame\Pattern\Factory;

class Method extends Factory implements MethodInterface {

    protected bool $hasTokenRequirement = false;
    protected string $AuthUser;
    protected string $AuthToken;

    protected bool $hasDbREquirement = false;
    protected DBHelper $DBHelper;
    protected string $DBTable;

    public function run() {
        if($this->hasTokenRequirement) {
            $this->pullToken();
        }


    }

    public function authenticate(): bool
    {
        // TODO: Implement authenticate() method.
    }

    public function isAuthenticated(): bool
    {
        // TODO: Implement isAuthenticated() method.
    }

    public function veto(): void
    {
        // TODO: Implement veto() method.
    }

    public function bySessionCookie(): bool
    {
        // TODO: Implement bySessionCookie() method.
    }
}