<?php

namespace App\Exceptions;

class TelegramException extends \Exception {

    public function __construct($exmsg, $val = 1, \Exception $old = null) {
        $exmsg = 'Default';
        parent::__construct($exmsg, $val, $old);
    }

    // representing the custom string object
    public function __toString() {
        return "Code: [{$this->code}] \n Line: {$this->getLine()}";
    }

    public function custFunc() {
        echo "Insert any custom message here\n";
    }
}
