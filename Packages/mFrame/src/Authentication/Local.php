<?php

namespace mFrame\Authentication;

use mFrame\Pattern\Factory;
use mFrame\Axis\Model;

class Local extends Model {
    protected string $table = "Users";


    public function pullUser(string $Username, string $Password) {
        $data = $this->db->obtain(
            $this->db->select("*") .
            $this->db->setClause(
                [
                    "username" => $Username,
                    "password" => sha1( cipher("encrypt", $Password) ),
                ]
            ),
        );
    }
}