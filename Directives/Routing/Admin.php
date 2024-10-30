<?php

use mFrame\Uri\Router;

Router::Add("GET", "/", function(){
    return Router::RouteReturn(
        "mSel\\Controllers\\Dashboard"
    );
});
