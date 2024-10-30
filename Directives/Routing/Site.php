<?php

use mFrame\Uri\Router;
use mFrame\Uri\Request;

Router::Add("GET", "/", function(){
    return Router::RouteReturn(
        "login",
    );
});

Router::Add("GET", "/setup", function(){
   return Router::RouteReturn(
       "Setup",
   );
});

Router::Add("POST", "/", function(){
    return Router::RouteReturn(
        "login",
        "login",
        Request::SanitizePost($_POST) ?: array(),
    );
});

Router::Add("POST", "/setup", function(){
    return Router::RouteReturn(
        "Setup",
        "configure",
        Request::SanitizePost($_POST) ?: array()
    );
});