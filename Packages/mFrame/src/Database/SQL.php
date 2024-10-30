<?php

namespace mFrame\Database;

use mFrame\Pattern\Factory;
use PDO;
use PDOException;

class SQL extends Factory {

    private bool $multiple;
    private array $connections;

    public mixed $primary;

    public function run(){
        //need to pop the primary
        $primary = $this->connections["primary"];
        $primary_dsn = $this->set_dns($primary["driver"], $primary["host"], $primary["port"]);
        $this->connect("primary", $primary_dsn, $primary["username"], $primary["password"]);

        if($this->multiple){
            unset($this->connections["primary"]);
            foreach($this->connections as $pointer => $db){
                $db_dsn = $this->set_dns($db["driver"], $db["host"], $db["port"]);
                $this->connect($pointer, $db_dsn, $db["username"], $db["password"]);
            }
        }


    }

    private function set_dns(string $driver, string $host, string $database, string $port = "") : bool | string {
        switch($driver) {
            case "sqlite": $values = array("sqlite:", $host); break;
            case "mssql": $values = array("sqlsrv:", "Server=" . $host, ",Port=" . $port, ",Database=" . $database); break;
            default: $values = array("mysql:", "host=" . $host, ";port=" . $port, ";dbname=" . $database); break;
        }

        if(empty($port) && $driver !== "sqlite"){
            unset($values[2]);
        }

        return implode("", $values);
    }

    public function connect(string $pointer, string $dsn, string $user, string $pass) : bool {
        if($this->validate($pointer)){
            try {
                $this->push($pointer, new PDO($dsn, $user, $pass));
                return true;
            } catch (PDOException $e) {
                terminate($e->getMessage());
            }
        }
    }

}