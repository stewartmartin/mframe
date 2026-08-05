<?php

namespace mFrame\Pattern;

use mframe\Base\Configuration;
use mFrame\Base\Mutator;

#[\AllowDynamicProperties]
abstract class Factory {

    use Configuration, Mutator;

    public function __construct(mixed $params = ""){
        //The constructor here needs to be completely rethought.
        if(!empty($params)){
            if(is_array($params)){
                self::$rawConfig = $params;
                self::processConfigs();
            } else {
                foreach ($params as $directive => $value) {
                    if ($this->validate($directive)) {
                        $this->push($directive, $value);
                    }
                }
            }
        }
        if(method_exists($this, "loadDirectives")){
            $this->loadDirectives();
        }

        if(method_exists($this, "run")){
            $this->run();
        }
    }

    abstract public function run();
    abstract protected function loadDirectives(string $container = "", string $directive = "", string $subDirective = "") : bool;

}