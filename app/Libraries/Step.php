<?php

namespace Libraries;

class Step {

    public static array $steps = [];

    public static function set(?string $updates, $type): void {
        self::$steps[$type] = $updates;
    }

    /**
     * @return object
     */
    public static function callback(): ?string {
        return self::$steps['step'];
    }


}