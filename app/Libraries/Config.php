<?php

namespace Libraries;

class Config {

    const CONFIG_DELIMITER = '.';

    /**
     * @param array $array
     * @param string $propname
     * 
     * @return array|string|null
     */
    public static function getProp(array $array, string $propname) {   
        foreach(explode('.', $propname) as $node) {
            if(isset($array[$node]))
                $array = &$array[$node];
            else
                return $array;
        }
        return $array;
    }

    /**
     * @param string $array
     * 
     * @return array|string|bool
     */
    public static function get(string $array) {

        $path = realpath(dirname(__FILE__, 3)).'/config/';
        $parts = explode(self::CONFIG_DELIMITER, strtolower($array));
        $keys = substr($array, strlen($parts[0]) + 1);

        if(file_exists($path . $parts[0] . '.php')) {
            $array = require $path . $parts[0] . '.php';
            
            if(count($parts) == 1) {
                return $array;
            } else {
                return self::getProp($array, $keys);
            }
        } else {
            return false;
        }
    }

}