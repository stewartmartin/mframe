<?php

/*
 * mFrame uses database sessions. So that we can track historical logins.
 * This is generally a requirement within large fortune 500 organizations whom do not understand
 * SOX or PCI as they are written as catch alls.
 */

namespace mFrame\Authentication;

use mFrame\Pattern\Factory;
use mFrame\Axis\Model;

class Session extends Factory {

    protected string $method = "db";
    protected mixed $accessor = null;
    protected string $sessionID;
    protected mixed $sessionData;
    protected int $lifespan;

    public function run() : void {
        if($this->method == "db"){
            $this->accessor = new Model(["table" => "session"]);
        }

        session_set_save_handler(
            array($this, "open"),
            array($this, "close"),
            array($this, "read"),
            array($this, "write"),
            array($this, "destroy"),
            array($this, "gc")
        );

        session_start();

    }

    public function open() : bool {
        return !is_null($this->accessor);
    }

    public function close() : bool {
        if(!is_null($this->accessor)) {
            $this->accessor = null;
            return true;
        }
        return false;
    }

    public function read(mixed $sessionID) : mixed {
        $this->sessionID = $sessionID;
        $find_session = $this->accessor->obtain(
            $this->accessor->read($this->sessionID) . $this->accessor->setClause(["session_id", "=", $this->sessionID ])
        );
        if($find_session) {
            return json_decode($find_session["data"], true);
        }

        return false;
    }

    public function write(mixed $session_id, mixed $session_data) : bool {
        $access = time();
        $this->sessionID = $session_id;

        if(is_object($session_data)){
            $session_data = toArray($session_data);
        }

        $session_data = json_encode($session_id);

        return $this->accessor->obtain(
            $this->accessor->update(["data" => $session_data, "access_time" => $access]) .
            $this->accessor->setClause(["session_id", "=", $session_id])
        );
    }
    public function destroy($session_id) : bool {
        $this->accessor->protected = false;
        return $this->accessor->obtain(
            $this->accessor->delete() .
            $this->accessor->setClause(["session_id", "=", $session_id])
        );
    }

    public function gc() : bool {
        $this->accessor->protected = false;
        $expired = time() - $this->lifespan;
        return $this->accessor->obtain(
            $this->accessor->delete() .
            $this->accessor->setClause(["access_time", "<=", $expired])
        );
    }
}