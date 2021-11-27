<?php

namespace Libraries;

class Terminal {

    private static $path;

    /**
     * @return void
     */
    private static function createPath(): void {
        self::$path = realpath(dirname(__FILE__, 2))."/Helpers/terminal/";
    }

    /**
     * @param string $path
     * @param array $data
     * @param bool $async
     * 
     * @return void
     */
    public static function send(string $path, array $data = [], bool $async = true): void {

        self::createPath();

        $is_async = '';

        if($async) {
            $is_async = '> /dev/null 2>&1 &';
        }
        
        $parameters = base64_encode(json_encode($data));
        shell_exec("php " . self::$path . $path . ".php \"$parameters\" $is_async");
    }

    /**
     * @param mixed $content
     * 
     * @return object
     */
    public static function receive($content, bool $base_decode = true) {

        if($base_decode) {
            $decode = base64_decode($content);
            $array_convert = json_decode($decode);
        } else {
            $array_convert = json_decode(json_encode($content));
        }
        
        return $array_convert;
    }

}