<?php

use mFrame\Uri\Router;
use mFrame\Uri\Request;

Router::Add("GET", "/login", function(){
    return Router::RouteReturn(
        "Login",
        "index",
        Request::SanitizePost($_POST) ?: array(),
    );
});

Router::Add("POST", "/login", function(){
    return Router::RouteReturn(
        "Login",
        "process",
        Request::SanitizePost($_POST) ?: array(),
    );
});

Router::Add("GET", "/logout", function(){
    return Router::RouteReturn("Login", "logout");
});

Router::Add("GET", "/register", function(){
    return Router::RouteReturn("Login", "register");
});

Router::Add("POST", "/register", function(){
    return Router::RouteReturn("Login", "signup", Request::SanitizePost($_POST) ?: array());
});