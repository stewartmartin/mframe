<?php

namespace mFrame\Authentication\Helpers;

use mFrame\Pattern\Factory;
use Exception;

class Token extends Factory {

    private string $signing_key;

    public function run() : void {
        if(!array_key_exists("jwt_key", self::$rawConfig)){
            $jwt_key = self::getDirective("Session", "JWT_KEY");
        } else {
            $jwt_key = self::$rawConfig["jwt_key"];
        }
        $this->signing_key = str_replace(['base64:', '='], '', $jwt_key);
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

        return [ 'header' => $header, 'body' => $payload ];
    }

}
