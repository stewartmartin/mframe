<?php

namespace mFrame\Helpers;

use mFrame\Pattern\Singleton;


//This should be renamed to "CronTask" as "Cron" and "cli" should handle the base lifting.
abstract class CronTask extends Singleton {

    protected static array $requiredPackages = [];

    public static function run(): void {
        $task = get_called_class();
        if(method_exists($task, "start")){
            $task::start();
        }
    }

    abstract public static function start();

}