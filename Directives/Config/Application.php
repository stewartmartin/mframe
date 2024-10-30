<?php

return array(
    "name" => "",
    "slogan" => "",
    "domain_schmea" => "https",
    "root_domain" => "whatever.com",
    "routing" => array(
        "method" => "path",
        "directives" => array(
            "admin" => array(
                "restricted" => true,
                "path" => "control-panel/",
                "routes_file" => ""
            ),
            "member" => array(
                "restricted" => true,
                "path" => "member/",
                "routes_file" => ""
            ),
            "team" => array(
                "restricted" => true,
                "path" => "team/",
                "routes_file" => ""
            ),
            "site" => array(
                "restricted" => false,
                "path" => "",
                "routes_file" => ""
            )
        ),
    ),
);