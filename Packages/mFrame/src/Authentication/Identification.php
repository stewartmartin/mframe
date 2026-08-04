<?php

namespace mFrame\Authentication;

use mFrame\Authentication\Helpers\{Ban, Session, Cookie, Token};
use mFrame\Authentication\Methods\{Local, LDAP, Google, Meta};
use mFrame\Pattern\Factory;

class Identification extends Factory {

    public Session $Session;
    public Cookie $Cookie;
    public LDAP | Local | Google | Meta $Method;
    public Ban $Ban;
    public Token $Token;
    public int $attempt = 1;
    public int $maxAttempts = 3;
    public bool $banned = false;
    public bool $authRequired = false;

    public function run() : bool {
        if(strtolower(self::getDirective("Authentication", "Method")) == "ldap"){
            $method = "LDAP";
        } else {
            $method = ucfirst(self::getDirective("Authentication", "Method"));
        }

        if(!is_null( $this->Cookie->getCookieData("Banned") ) ){
            terminate("You have been banned from this application.");
        }

        $this->Ban = new Ban();
        if($this->Ban->checkBan()){
            terminate("You have been banned from this application.");
        }

        $this->Method = new $method();

        $this->Session = new Session();
        $this->Cookie = new Cookie();

        $this->Token = new Token( self::getDirective("Authentication", "Token") );

        return $this->checkStatus();
    }

    public function authenticate(): bool {
        if(isset($_POST["username"]) && isset($_POST["password"])){
            $user = $_POST["username"];
            $pass = $_POST["password"];

            return $this->Method->authenticate($user, $pass);
        }

        return false;
    }
    
    public function isAuthenticated(): bool {
        if(is_string($this->checkStatus())){
            return true;
        }

        return false;
    }

    public function veto(): void {
        $this->Method->veto();
    }

    protected function checkStatus(): bool {
        return $this->Method->bySessionCookie();
    }

}