<?php

/*
 * mFrame is technically not a package. However, we are leaving this here in case a user decides to use another framework
 * For package development. please refer to the boilerplate below for how to create your entry.

namespace mFrame;

use mFrame\Helpers\Package;

$package_path = realpath( dirname(__FILE__) ) . DIRECTORY_SEPARATOR;

if(empty($directives) && file_exists($package_path . "package.json")){
    $directives = loadJSON($package_path . "package.json");
}

if(!empty($directives)) {
    $directives["path"] = $package_path;
    $mFrame = Package::initiate($directives);
}

*/

autoload(realpath( dirname(__FILE__) . 'src' . DIRECTORY_SEPARATOR));