<?php

//set the root, Public and the rest shall be handled by frontal incase of customization.
define("ROOT", dirname(__FILE__, 2) . DIRECTORY_SEPARATOR );

$startupFiles = array("functions" => "required functions file", "frontal" => "required frontal file");
foreach($startupFiles as $file => $message){
    $path = ROOT . $file . ".php";
    if(!file_exists($path)){
        require_once(ROOT. $file . ".php");
    } else {
        die("Missing " . $message);
    }
}

use mSkel\frontal;
if(class_exists("frontal")) {
    if (frontal::SysCheck()) {
        $App = new frontal();
    }
}