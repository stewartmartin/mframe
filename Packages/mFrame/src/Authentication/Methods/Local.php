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
        }
        $this->Database = new DBHelper( ["Table" => self::getDirective("Authentication", "UsersTable") ] );
        if($this->Database->connected){
            $this->Pointer = $this->Database->getPointer();
        }

        return false;
    }

    public function authenticate(string $Username, string $Password) : bool {
        $username = filter_var($Username, FILTER_SANITIZE_STRING);
        $password = password_hash(
            hash_hmac( "sha256", filter_var($Password, FILTER_SANITIZE_STRING), self::getDirective("Authentication", "Cypher")
            ), PASSWORD_DEFAULT );

        $this->user = $this->Model->obtain(
            $this->Model->select("*") .
            $this->Model->setClause(
                ["username" => $username, "password" => $password],
            )
        );

        return $this->isAuthenticated();
    }

    public function isAuthenticated() : bool{
        if(!empty($this->user)) {
            $user = $this->user;

            if (is_object($user)) {
                $user = toArray($this->user);
            }

            return array_key_exists("id", $user);
        }

        return false;
    }

    public function veto() : void{
        unset($this->Database);
        unset($this->Model);
        unset($this->user);
    }

    public function bySessionCookie(string $username) : bool {
        $user = filter_var($username, FILTER_SANITIZE_STRING);
        $this->user = $this->Model->Obtain(
            $this->Model->select("*") .
            $this->Model->setClause(["username" => $username]),
        );

        return $this->isAuthenticated();
    }

}