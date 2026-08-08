<?php

use mFrame\Uri\Router;
use mFrame\Uri\Request;

if(defined("Auth")){
    Auth->setAuth();
    if(!Auth->isAuthenticated()){
        return Router::RouteReturn(
            "Error",
            "Unauthorized",
        );
    }
}