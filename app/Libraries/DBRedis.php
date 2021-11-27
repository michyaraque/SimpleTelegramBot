<?php

namespace App\Libraries;

class DBRedis extends \Redis {

    private $host = 'localhost';
    private $port = 6379;
    private $database = 13;

    public function __construct() {
        $this->connect($this->host, $this->port);
        $this->select($this->database);
        return $this;
    }
}