<?php

return array(
    "name" => "",
    "slogan" => "",
    "domain_schmea" => "https",
    "root_domain" => "whatever.com",
    "routing" => array(
        "method" => "path",
        "directives" => array(
            //A path can be one of three things. Path (folder/URI), port, or subdomain. If subdomain, the "entry" must be subdomain.
            "method" => "path",
            "admin" => array(
                "restricted" => true,
                "entry" => "control-panel",
                "routes_file" => "Admin.php"
            ),
            "member" => array(
                "restricted" => true,
                "entry" => "member",
                "routes_file" => ["site.php", "Member.php"],
            ),
            "site" => array(
                "restricted" => false,
                "entry" => "/",
                "routes_file" => "site.php"
            ),
            "api" => array(
                "restricted" => false,
                "entry" => "api",
                "routes_file" => "Api.php"
            )
        ),
    ),
);