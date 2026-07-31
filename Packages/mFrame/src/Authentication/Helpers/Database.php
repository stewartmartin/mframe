<?php

namespace mFrame\Authentication\Helpers;

use mFrame\Database\SQL;
use mFrame\Axis\Model;
use mFrame\Pattern\Factory;
use Exception;

class Database extends Factory {

    protected SQL $Database;
    protected Model $Model;
    protected string $Table;

    public bool $connected = false;

    public function run() : bool {
        if(empty($this->Table)){
            terminate("Table not supplied for Authentication mechanisms");
        }

        $this->Database = new SQL( self::getDirective("Database") );
        if($this->Database->connected){
            try {
                $this->Model = new Model(
                    [
                        "db" => $this->Database,
                        "table" => $this->Table,
                    ]);
                $this->connected = true;
                return true;
            } catch (Exception $e) {
                terminate("There was an Auth error: " . $e->getMessage() );
            }
        }

        return false;
    }

    public function getPointer() : bool | Object {
        if(isset($this->Model)){
            return $this->Model;
        }

        return false;
    }

}

