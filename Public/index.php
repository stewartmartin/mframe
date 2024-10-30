<?php

//We can go ahead and handle 2 defines. Root and Public. These will be used throughout the app, and eventually given to
//The framework to process the other directory as either superglobals, or as static methods to return a path.
define("ROOT", dirname(__FILE__, 2) . DIRECTORY_SEPARATOR );
define("PUBLIC", realpath( dirname( __FILE__) ) . DIRECTORY_SEPARATOR);

//From here we need two files. The first we are going to check for is the functions.php file.
if(!file_exists(ROOT . "functions.php")){
    die("Missing required functions. Can not continue.");
}

//The second file is our frontController. This file is what you could call "application-router" It kick starts the app,
//checks the route, processes the request, and then handles the page rendering through the framework rendering engine.
if(!file_exists(ROOT . "frontal.php")){
    die("Missing required frontal. Can not continue.");
}

//Our files exist, lets include them and get this show on the road.
require_once(ROOT . "functions.php");
require_once(ROOT . "frontal.php");

use mSkel\frontal;

//If you would like a custom directory structure, please look at the default defined array within the directives function.
//Take the default array and change only the values of what you want the folders to be. Then you need to pass that in the
//Static directives function. Here I am passing an empty array to ensure the default is used.
frontal::setStructure([]);

//From here, the frontal can will handle/route/and process everything else.
$frontal = new Frontal();