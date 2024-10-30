<?php

namespace mFrame\Helpers;

use mFrame\Pattern\Singleton;

class Package extends Singleton {

    public static string $label;
    public static String $description;
    public static string $version;
    public static array $autoload;
    public static mixed $license;
    public static mixed $authors;

    public static string $path;
    public static array $directives;

    public static function run() : void { }

    public static function directives(array $required_directives) : mixed {
        foreach ($required_directives as $directive => $value) {
            static::pushStatic($directive, $value);
        }
    }
}