<?php

namespace Telegram;

class UpdateObject {

    /**
     * @param object|null $data
     */
    public function __construct(?object $data) {
        return $this->setData($data);
    }

    /**
     * @param object|null $data
     * 
     * @return string
     */
    public function setData(?object $data): string {
        return json_encode($data);
    }
}