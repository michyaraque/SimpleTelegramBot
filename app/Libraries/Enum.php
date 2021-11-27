<?php

namespace App\Libraries;

abstract class Enum {
    
    private final function __construct(){ }

    /**
     * @param mixed $val
     * 
     * @return string
     */
    public static function toString($val): string {
        $tmp = new \ReflectionClass(get_called_class());
        $a = $tmp->getConstants();
        $b = array_flip($a);

        return ucfirst(strtolower($b[$val]));
    }
}