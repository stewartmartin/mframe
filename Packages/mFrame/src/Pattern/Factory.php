<?php

namespace mFrame\Pattern;

use mframe\Base\Configuration;
use mFrame\Base\Mutator;

#[\AllowDynamicProperties]
abstract class Factory {

    use Configuration, Mutator;

    public function __construct(mixed $params = ""){
        if(!empty($params)){
            foreach($params as $directive => $value){
                if($this->validate($directive)){
                    $this->push($directive, $value);
                }
            }
        }

        if(method_exists($this, "run")){
            $this->run();
        }
    }

    abstract public function run();

}