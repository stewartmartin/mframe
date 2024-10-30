<?php

function autoload(string $abs_path, bool $recursive = true) : bool {
    if(is_dir($abs_path) && is_readable($abs_path)){
        $contents = realScan($abs_path);
        foreach(realScan($abs_path) as $item){
            $item_path = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $abs_path . DIRECTORY_SEPARATOR . $item);
            if(is_dir($item_path) && $recursive){
                autoload($item_path, $recursive);
            } elseif(file_exists($item_path) && validate_file($item_path)){
                require_once($item_path);
            } else {
                //It's not a valid folder, file, or it's not an accepted file type.
                terminate("Autoload failure on: " . $item_path);
            }
        }
        //If we got this far without a failure, we are assuming we're good to go.
        return true;
    }
    return false;
}

function realScan(string $abs_path) : array | bool {
    if(is_dir($abs_path)){
        return array_diff(scandir($abs_path, ['.', '..']));
    }
    return false;
}

function validate_file(string $file_name) : bool {
    $approved_extensions = ['php', 'phtml', 'phar', 'inc'];
    if(is_file($file_name) && in_array(pathinfo($file_name, PATHINFO_EXTENSION), $approved_extensions)){
        return true;
    }
    return false;
}

function toArray(object $objectToConvert) : array | bool {
    if( count( (array)$objectToConvert ) ){
        return json_decode(json_encode($objectToConvert),true);
    }
    return false;
}

function toObject(array $arrayToConvert) : object | bool {
    if(!empty($arrayToConvert)){
        return json_decode(json_encode($arrayToConvert));
    }
    return false;
}

function terminate(string $message, bool $print_backtrace = true) : void {
    $error = $message;
    if($print_backtrace){
        $error .= "<br /><br /><pre>" . print_r(debug_backtrace(), true) . "</pre>";
    }
    die($error);
}

function cipher(string $direction, $data, $algo = "default") : mixed {
    if($algo == "default"){
        $algo = "aes-256-ctr";
    }

    if(defined("ENCRYPTION_TOKEN")){
        $token = ENCRYPTION_TOKEN;
    } else {
        $token = "";
    }

    if(in_array($algo, openssl_get_cipher_methods())){
        $size = openssl_cipher_iv_length($algo);
        $bites = openssl_random_pseudo_bytes($size);
        $bin = hex2bin($algo);
        if(in_array($direction, array("encrypt", "en", "e"))){
            return openssl_encrypt($data, $algo, $bin, OPENSSL_RAW_DATA, $token);
        }

        if(in_array($direction, array("decrypt", "de", "d"))){
            $parse_hash = mb_substr($algo, $bites, null, "8bit");
            return openssl_decrypt($parse_hash, $algo, $bin, OPENSSL_RAW_DATA, $token);
        }
    }

    return false;
}

function extractName(string $path_to_class, string $class_or_space = "class") : string | bool {
    if(file_exists($path_to_class)){
        $handle = fopen($path_to_class, "r");
        if($handle){
            while($line = fgets($handle) !== false){
                $parse_line = explode(" ", $line);

                if(in_array(strtolower($class_or_space), array("class", "cls", "c"))){
                    if(strtolower($parse_line[0]) == "class"){
                        return $parse_line[1];
                    }
                }

                if(in_array(strtolower($class_or_space), array("namespace", "name", "ns", "n"))){
                    if(strtolower($parse_line[0]) == "namespace"){
                        return rtrim($parse_line[1], ";");
                    }
                }
            }
        }
    }

    return false;
}

function loadJSON(string $path_to_json, bool $asObject = false) : bool | array {
    if(file_exists($path_to_json)){
        $contents = file_get_contents($path_to_json);
        $data = json_decode($contents, true);
        if($asObject){
            $data = toObject($data);
        }

        return $data;
    }

    return false;
}