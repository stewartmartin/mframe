<?php

namespace mFrame\Helpers;

use mFrame\Pattern\Factory;
use mFrame\Authentication\Local;
use mFrame\Authentication\LDAP;
use mFrame\Authentication\Cookie;
use mFrame\Authentication\Session;

class User extends Factory {

    protected string $method = "local";
    protected mixed $methodObject = null;
    protected mixed $cookieObject = null;
    protected mixed $sessionObject = null;

    public int $user_id;
    public string $user_email;
    public int $user_type;

    public string $user_name;
    public bool $user_validated = false;

    public array $group_assignments = [];

    protected array $user_types = array(
        array("superadmin", "Super Admin"),
        array("leader", "Leader"),
        array("member", "Member"),
        array("user", "User")
    );

    public function run() : void {
        if($this->method == "local"){
            $this->methodObject = new Local([]);
        } elseif ($this->method == "LDAP"){
            $this->methodObject = new LDAP([]);
        } else {
            terminate("Oh snap, our auth is all jacked up.");
        }

        $this->cookieObject = new Cookie( self::getDirective("Sessions", "Cookie") );
        $this->sessionObject = new Session([]);
        $this->checkAuthStatus();
    }

    protected function checkAuthStatus() : void {
        if($this->cookieObject->validateCookie()){
            $this->setUser($this->cookieObject->cookie_data);
        }
    }

    public function setUser(mixed $UserObject) : bool {
        if(is_object($UserObject)){
            $UserObject = toArray($UserObject);
        }

        if(is_array($UserObject) && array_keys($UserObject) == array_keys(static::ObjectMap())){
            foreach($UserObject as $key => $data){
                if($this->push($key, $data)){
                    continue;
                }

                terminate("The user data supplied is invalid.");
            }

            $_SESSION["data"] = json_encode(toArray($this));

            return true;
        }

        return false;
    }

    public static function ObjectMap() : array {
        return array(
            "user_id" => "",
            "user_email" => "",
            "user_type" => "",
            "user_name" => "",
            "user_validated" => false,
            "group_assignments" => array(),
        );
    }

}