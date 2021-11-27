<?php

namespace Libraries;

class Lang {

    const LANG_DELIMITER = '.';
    
    public static $lang = 'en';

    public static $fallback = 'es';

    private static $memoized_arr;

    public static function set(string $lang = 'en'): void {
        self::$lang = $lang; 
    }

    public static function getProp(array $array, string $propname) {   
        foreach(explode('.', $propname) as $node) {
            if(isset($array[$node]))
                $array = &$array[$node];
            else
                return $array;
        }
        return $array;
    }

    public static function get(string $array, array $binders = []) {
        $path = realpath(dirname(__FILE__, 3)).'/resources/lang/'.self::$lang.'/';
        $parts = explode(self::LANG_DELIMITER, strtolower($array));
        $keys = substr($array, strlen($parts[0]) + 1);

        $file = $parts[0];

        if (!file_exists($path . $file . '.php')) {
            $path = realpath(dirname(__FILE__, 3)).'/resources/lang/' . self::$fallback . '/';
        }

        if(file_exists($path . $file . '.php')) {

            if(!empty(self::$memoized_arr[$file])) {
                $array = self::$memoized_arr[$file];
            } else {
                $array = require_once $path . $file . '.php';
                self::$memoized_arr[$file] = $array;
            }

            if(count($parts) == 1) {
                return $array;
            } else {

                $array = self::getProp($array, $keys);

                if(sizeof($binders) !== 0) {
                    foreach($binders as $bind => $value) {
                        $array = preg_replace("/:$bind/", $value, $array);
                    }
                }
                
                return $array;
            }
        }
        
        return false;
    }

    /**
     * @return string|null
     */
    public static function getActualLang(): ?string {
        return self::$lang;
    }

    /**
     * @return array
     */
    public static function languageList(): array {
        $dir = realpath(dirname(__FILE__, 3)).'/resources/lang/';
        return array_values(array_diff(scandir($dir), ['..', '.']));
    }

}