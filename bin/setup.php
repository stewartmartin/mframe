<?php

define("ROOT", dirname(__FILE__) . DIRECTORY_SEPARATOR);
$requiredBase = ["functions.php", "frontal.php"];
foreach($requiredBase as $file){
    if(!file_exists(ROOT . $file)){
        die("Could not locate required file: " . $file);
    }
    require_once(ROOT . $file);
}


