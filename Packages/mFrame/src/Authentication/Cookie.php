<?php

namespace mFrame\Authentication;

use mFrame\Pattern\Factory;

class Cookie extends Factory {

    protected string $cookie_name;
    protected mixed $cookie_data = null;
    protected int $cookie_expire = 0;


    public function run(){
        if(is_null($this->cookie_data)){
            $this->pullCookie();
        }
    }

    public function pullCookie() : bool {
        if($_COOKIE[$this->cookie_name]){
            $cookie = $_COOKIE[$this->cookie_name];
            $this->cookie_data = json_decode(cipher("decode", $cookie), true);
            return true;
        }

        return false;
    }

    public function validateCookie() : bool {
        if($_COOKIE[$this->cookie_name]){
            return true;
        }

        return false;
    }

    public function pushCookie(mixed $data, mixed $time = "") : bool {
        if(is_object($data)){
            $data = toArray($data);
        }

        $data = cipher("encrypt", $data);

        $default_time = time() + 86400;
        if(is_string($time)){
            $time = strtotime("24 hours");
            if(!empty($time)){
                $time = strtotime($time);
            }
        }

        if(!is_string($time)){
            $time = $default_time;
        }

        if(setcookie($this->cookie_name, $data, $time, "/", $_SERVER["SERVER_NAME"], true)){
            return true;
        }

        return false;
    }
}