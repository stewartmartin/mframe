<?php

namespace mFrame\Authentication\Methods;

use mFrame\Authentication\Interfaces\MethodInterface;
use mFrame\Authentication\Helpers\Database as DBHelper;
use mFrame\Pattern\Factory;
use Exception;

class Meta extends Factory implements MethodInterface {

    private string $SecretAuth = "";
    private string $SecretToken = "";


    public function run() {
        // TODO: Implement run() method.
    }

    public function authenticate(): bool {
        // TODO: Implement authenticate() method.
    }

    public function isAuthenticated(): bool {
        // TODO: Implement isAuthenticated() method.
    }

    public function veto(): void {
        // TODO: Implement veto() method.
    }

    public function bySessionCookie() : bool {

    }

}