<?php

namespace mFrame\Base;

use ReflectionClass, ReflectionProperty, ReflectionException;

trait Mutator {

    //Set and return Reflectors
    protected function reflect() : object | bool {
        try {
            return new ReflectionClass($this);
        } catch (ReflectionException $e){
            die( "Could not set reflection: " . $e->getMessage() );
        }
    }

    protected static function reflectStatic() : object | bool {
        $class = get_called_class();
        try {
            return new ReflectionClass($class);
        } catch (ReflectionException $e){
            die( "Could not set static reflection: " . $e->getMessage() );
        }

    }

    //mFrame uses "validate" in place of "has", the below functions checks to see if a class has a property.
    //There are no plans at this time for method checks.
    public function validate($what) : bool {
        $reflection = $this->reflect();
        if($reflection->hasProperty($what)){
            return true;
        }
        return false;
    }

    public static function validateStatic($what) : bool {
        $reflection = static::reflectStatic();
        if( in_array($what, $reflection->getStaticPropertys()) ){
            return true;
        }
        return false;
    }

    //mframe uses pull in place of "get", the below function validates the property exist, grabs the value, and then sends it back.
    public function pull($what) : mixed {
        if($this->validate($what)){
            try {
                $property = new ReflectionProperty(get_called_class(), $what);
                return $property->getValue( $this->reflect() );
            } catch (ReflectionException $e){
                die("Could not get reflection of property: " . $e->getMessage() );
            }
        }
        return false;
    }

    public static function pullStatic($what) : mixed {
        if(static::validateStatic($what)){
            //ReflectionProperty is not required for static properties.
            $reflection = static::reflectStatic();
            return $reflection->getStaticPropertyValue($what);
        }
        return false;
    }

    //mframe uses the term push in place of "set"
    public function push($what, $value) : bool {
        if($this->validate($what)){
            try {
                $reflectProperty = new ReflectionProperty(get_called_class(), $what);
                $reflectProperty->setValue($this->reflect(), $value);
                return true;
            } catch (ReflectionException $e){
                die("Could not get reflection of property: " . $e->getMessage());
            }
        }
        return false;
    }

    public static function pushStatic($what, $value) : bool {
        if(static::validateStatic($what)){
            $reflection = static::reflectStatic();
            $reflection->setStaticPropertyValue($what, $value);
            return true;
        }
        return false;
    }

}