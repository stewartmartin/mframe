<?php

namespace mFrame\Authentication\Helpers;

use mFrame\Pattern\Factory;
use Exception;

class Token extends Factory {

    private string $signing_key;
    private string $header = "Authorization";
    public string $token;
    public bool $hasToken = false;

    public function run() : void {
        if(empty(self::$rawConfig)){
            self::$rawConfig = self::getDirective("Session");
        }

        if(array_key_exists("jwt_key", self::$rawConfig) && !empty(self::$rawConfig["jwt_key"])){
            $this->signing_key = str_replace(['base64:', '='], '', self::$rawConfig["jwt_key"]);
            if(array_key_exists("AuthHeader", self::$rawConfig)){
                $this->header = self::$rawConfig["AuthHeader"];
            }
        }
    }

    public function encode(array $payload) : string {
        $header = [ "alg" => "HS512", "typ" => "JWT" ];

        $encodedHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $encodedPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signature = hash_hmac('sha512', "$encodedHeader.$encodedPayload", $this->signing_key, true);
        $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "{$encodedHeader}.{$encodedPayload}.{$encodedSignature}";
    }

    public function decode(string $token) : ?array {
        $tokenParts = explode('.', $token);

        if (count($tokenParts) !== 3) {
            return null; // Invalid JWT format
        }

        list($encodedHeader, $encodedPayload, $providedSignature) = $tokenParts;

        $header = json_decode(base64_decode(strtr($encodedHeader, '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($encodedPayload, '-_', '+/')), true);
        $signature = base64_decode(strtr($providedSignature, '-_', '+/'));

        if ($header === null || $payload === null) {
            return null;
        }

        $validSignature = hash_hmac('sha512', "$encodedHeader.$encodedPayload", $this->signing_key, true);

        if (!hash_equals($signature, $validSignature)) {
            return null;
        }
        $this->hasToken = true;
        return [ 'header' => $header, 'body' => $payload ];
    }

    public function extractToken() : string | false {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (isset($_SERVER['Authorization'])) {
            $token = trim($_SERVER["Authorization"]);
        }

        if (!empty($token)) {
            if (preg_match('/Bearer\s(\S+)/i', $token, $matches)) {
                $this->token = $matches[1];
                return $this->token;
            }
        }

        return false;
    }

    public function compareToken(string $expectedToken) : bool {
        if($this->extractToken() == $expectedToken){
            return true;
        }
        return false;
    }

}
