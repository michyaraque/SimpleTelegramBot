<?php

namespace App\Libraries;

class Model {

    public static $static_connection;

    public static function create() {

        $dsn = sprintf('%s:dbname=%s;host=%s', env('DATABASE_DRIVER'), env('DATABASE_NAME'), env('DATABASE_HOST'));
        $options = [
            \PDO::ATTR_EMULATE_PREPARES => FALSE, 
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, 
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        try {
            $pdo = new \PDO($dsn, env('DATABASE_USERNAME'), env('DATABASE_PASSWORD'), $options);
            self::$static_connection = $pdo;
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function __destruct(){
        self::$static_connection = null;
    }

}