<?php

namespace mSkel\App\MiddleWare;

use mFrame\Authentication\Identification;
use mSkel\App\Models\{user_groups, users, user_map, user_geo, user_moniker, user_nativity, user_token};

class Auth extends Identification {

    protected users $user;
    protected user_map $user_map;
    protected user_geo $user_geo;
    protected user_moniker $user_moniker;
    protected user_nativity $user_nativity;
    protected user_token $user_token;

    public function setAuth(bool $isAuth = true): void {
        $this->authRequired = $isAuth;
    }

    public function checkStatus() : bool {
        if($this->Session->read($_SESSION["session_id"])){
            if(array_key_exists("user_id", $_SESSION)) {
                if($this->user->findById($_SESSION["user_id"]) || $this->user_token->findBy("user_id", $this->user->pull("user_id"))){
                    return true;
                }
            }
        }

        if($this->Cookie->pullCookie()){
            $cookie = $this->Cookie->getCookieData();
            if(array_key_exists("user_id", $cookie)){
                if($this->user->findById($cookie["user_id"])){
                    return true;
                }
            }
        }
        return false;
    }

    public function loginByCredentials(string $username, string $password): bool {
        return $this->authenticate($username, $password);
    }

    public function loginByToken(string $token): bool {
        if($this->user->validate("user_id")){
            $this->user_token = new user_token();
            $this->user_token->findBy("user_id", $this->user->pull("user_id"));
            if($this->user_token->validate("token")){
                return true;
            }
        }

        return false;
    }

    public function buildUser() : array | false {
        if($this->user->validate("user_id")){
            $this->user_map = new user_map();
            $this->user_map->findBy("user_id", $this->user->pull("user_id"));
            $this->user_geo = new user_geo();
            $this->user_geo->findById($this->user_map->pull("geo_id"));
            $this->user_groups = new user_groups();
            $this->user_groups->findBy("user_id", $this->user->pull("user_id"));
            $this->user_moniker = new user_moniker();
            $this->user_moniker->findById($this->user_map->pull("moniker_id"));
            $this->user_nativity = new user_nativity();
            $this->user_nativity->findById($this->user_map->pull("nativity_id"));
            $token = null;

            if($this->user_token->validate("token")){
                $token = $this->user_token;
            }

            return [
                "user" => $this->user,
                "map" => $this->user_map,
                "geo" => $this->user_geo,
                "groups" => $this->user_groups,
                "moniker" => $this->user_moniker,
                "nativity" => $this->user_nativity,
                "token" => $token,
            ];
        }
        return false;
    }

}