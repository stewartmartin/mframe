<?php

namespace mFrame\Authentication\Helpers;

use mFrame\Pattern\Factory;
use mFrame\Database\SQL;

class Ban extends Factory {

    //Options: file (stored in Storage\Ban\<ip.address>), or SQL.
    protected string $mode = "file";
    protected mixed $accessor = null;
    protected string $ip;


    public function run() : void {
        if(strtolower($this->mode) == "file"){
            $this->accessor = fopen(self::getDirective("Storage", "Ban"), "r");
        }

        if(strtolower($this->mode) == "SQL"){
            $connection = new SQL(self::getDirective("Database"));
            if($connection->connected){
                $this->accessor = $connection;
            }
        }

        $this->ip = self::getUserIPAddress();
    }

    public function checkBan() : bool {
        if(strtolower($this->mode) == "file"){
            return $this->checkBanFile();
        }

        if(strtolower($this->mode) == "SQL"){
            return $this->checkBanSQL();
        }

        return false;
    }

    protected function checkBanFile() : bool {
        $userBanFile = self::getDirective("Storage", "Ban") . $this->ip;
        if(file_exists($userBanFile)){
            $banSetOn = filemtime($userBanFile);

            clearstatcache();
            $banExpires = $banSetOn;
            if(filesize($userBanFile) >= 1){
                $lengthOfBan = file_get_contents($userBanFile);
                if($lengthOfBan == "life"){
                    return true;
                }

                $banExpires = (int) $lengthOfBan * 86400;
                if($banExpires < time()){
                    unlink($userBanFile);
                    return false;
                }

                return true;
            }
        }
        return false;
    }

    protected function checkBanSQL() : bool {
        $query = $this->accessor->query("SELECT * FROM `bans` WHERE `ip` = '{$this->ip}'");
        $query->execute();
        $result = $query->fetchAll();
        if(!empty($result)){
            $banData = $result[0];
            if($banData["expires"] == "life"){
                return true;
            }

            $banExpires = time() + ($banData["expires"] * 86400);
            if($banExpires < time()){
                $drop = $this->accessor->query("DELETE FROM `bans` WHERE `ip` = '{$this->ip}'");
                $drop->execute();
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    public function setBan(int $lengthOfBan = 0, string $reason = "excessive logins") : bool {
        if(strtolower($this->mode) == "file"){
            return $this->setBanFile($lengthOfBan, $reason);
        }

        if(strtolower($this->mode) == "SQL"){
            return $this->setBanSQL($lengthOfBan, $reason);
        }

        return false;
    }

    protected function setBanFile(int $length, string $reason) : bool {
        $userBanFile = self::getDirective("Storage", "Ban") . $this->ip;
        if(file_exists($userBanFile)){
            //This is a hacking attempt and fuck the fucking fuckers.
            return file_put_contents($userBanFile, "life");
        }

        if($length == 0){
            return touch($userBanFile);
        } else {
            return file_put_contents($userBanFile, $length);
        }
    }

    protected function setBanSQL(int $length, string $reason) : bool {
        $query = $this->accessor->query("INSERT INTO `bans` (`ip`, `expires`, `reason`) VALUES ('{$this->ip}', '{$length}', '{$reason}')");
        $query->execute();
        return true;
    }

    public static function getUserIPAddress() : string | bool {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                foreach (explode(',', $_SERVER[$header]) as $ip) {
                    $ip = trim($ip);
                    // Validate the IP layout format
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        return false;
    }

}