<?php

/*
 * If multiple databases are used for this application,
 *  - First change MULTIPLE to true.
 *  - each key following the parent configuration variable will be a named instance and that is how you will access/use it.
 * If a singular database is used, please update the "PRIMARY" sub array elements.
 */

$configuration = array(
    "multiple" => false,
    "connections" = array(
        "primary" => array(
            "driver" => "mysql",
            "host" => "localhost",
            "port" => "",
            "database" => "",
            "username" => "",
            "password" => "",
        ),
    ),
);