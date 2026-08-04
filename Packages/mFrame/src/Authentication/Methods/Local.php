<?php

namespace mFrame\Authentication\Methods;

use mFrame\Authentication\Interfaces\MethodInterface;
use mFrame\Authentication\Helpers\Database AS DBHelper;
use mFrame\Pattern\Factory;
use Exception;

class Local extends Factory implements MethodInterface {

    protected mixed $UserModel;

    protected array | object $user;

    public function run() : bool {
        if(array_key_exists("UserModel", self::$rawConfig)){
            $class = self::$rawConfig["UserModel"];
            $this->UserModel = new $class();
            return true;
        }

        return false;
    }

    public function authenticate(string $username, string $password) : bool {
        $username = filter_var($username);
        $password = password_hash(
            hash_hmac( "sha256", filter_var($password), self::getDirective("Authentication", "Cypher")
            ), PASSWORD_DEFAULT );

        $this->user = $this->UserModel->obtain(
            $this->UserModel->select("*") .
            $this->UserModel->setClause(
                ["username" => $username, "password" => $password],
            )
        );

        return $this->isAuthenticated();
    }

    public function isAuthenticated() : bool{
        return $this->user->has("user_id");
    }

    public function veto() : void {
        unset($this->Database);
        unset($this->UserModel);
        unset($this->user);
    }

    public function bySessionCookie() : bool {
        $cookie = $this->Cookie->getCookieData();
        if(array_key_exists("user_id", $cookie)){
            $this->user = $this->UserModel->findById($cookie["user_id"]);
        }

        if(array_key_exists("user_id", $_SESSION)){
            $this->user = $this->UserModel->findById($_SESSION["user_id"]);
        }

        return $this->isAuthenticated();
    }

}