<?php

namespace mSkel\App\MiddleWare;

use mFrame\Authentication\Identification;
use mSkel\App\Models\{users, user_map, user_geo, user_moniker, user_nativity, user_token};

class Auth extends Identification {

    protected users $user;
    protected user_map $user_map;
    protected user_geo $user_geo;
    protected user_moniker $user_moniker;
    protected user_nativity $user_nativity;
    protected user_token $user_token;

    protected function setAuth(bool $isAuth = true): void {
        $this->authRequired = $isAuth;
    }

    protected function loginByCredentials(string $username, string $password): bool {
        if ($this->user->findBy("username", filter_var($username))) {
            if (password_verify(filter_var($password), $this->user->pull("password"))) {
                $this->Cookie->pushCookie()
            }
        }
        return false;
    }

    protected function loginByToken(string $token): bool {

    }

}