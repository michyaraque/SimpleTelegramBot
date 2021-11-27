<?php

namespace Telegram\Traits;

use Telegram\Updates;

trait Tools {

    /**
     * @param int $param_selector
     * 
     * @return array|string
     */
    public static function getTextParams(?int $param_selector = null) {
        $updates = Updates::get();
        if(isset($updates->message->text)) {
            $parameters = explode(' ', $updates->message->text);
            if(!empty($parameters[$param_selector])) {
                return $parameters[$param_selector];
            } elseif(!isset($param_selector)) {
                return $parameters;
            }
        }
    }

    /**
     * @param string $command
     * 
     * @return bool
     */
    public static function getCommand(string $command): bool {
        $updates = Updates::get();
        if(isset($updates->message->text) && 
            isset($updates->message->entities[0]) && 
            $updates->message->entities[0]->type == 'bot_command' && 
            contains(strtolower($updates->message->text), $command)
        ) {
            $command_updates = explode(' ', trim(strtolower($updates->message->text)));
            return ('/' . $command == $command_updates[0] ? true : false);
        }
        
        return false;
    }

    /**
     * @param string|null $value
     * 
     * @return bool
     */
    public function getDeepLink(?string $value): bool {
        $updates = Updates::get();
        if(isset($updates->message->text) && 
            contains($updates->message->text, '/start') && 
            $updates->message->entities[0]->type == 'bot_command') {

            if(substr($updates->message->text, 7) === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param object $from_data
     * @param int $code
     * @param callable $content
     * 
     * @return bool
     */
    public function getErrorCode(object $from_data, int $code, callable $content): bool {
        if($from_data->ok == false && !empty($from_data->error_code) && $from_data->error_code === $code) {
            $content();
            return true;
        }
        return false;
    }

    
    /**
     * @param mixed $id
     * 
     * @return int
     */
    public function getMessageId(int $id): int {
        $update = (object) $id;
        $message_id = $update->result->message_id;
        return $message_id;
    }

    /**
     * @param string $string
     * @param mixed $output
     * 
     * @return bool
     */
    public static function matchText(string $string, ?string &$output = ''): bool {
        if(preg_match("/$string/", Updates::text(), $output)) {
            return true;
        }
        return false;
    }

}