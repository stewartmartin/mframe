<?php

use mFrame\Uri\Router;
use mFrame\Uri\Request;

Router::Add("GET", "/", function(){
    return Router::RouteReturn(
        "mSel\\Controllers\\Dashboard"
    );
});
