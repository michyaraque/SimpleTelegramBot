<?php

namespace Telegram\Traits;

use Telegram\Updates;

trait KeyboardUtils {

    private static $from_db = '';
    private static $parse_data = false;

    /**
	 * @param string $action
	 * @param array $value
	 * 
	 * @return array
	 */
	public static function createCallbackData(string $action, array $value): string {
        return join("|", array_merge([$action], $value));
    }

    /**
     * @param string $first_param
     * @param string $second_param
     * 
     * @return string
     */
    public static function createCallbackParam(string $first_param, string $second_param): string {
        return join(".", [$first_param, $second_param]);
    }

    public static function getCallbackArray() {
        $response = Updates::get();
        if(isset($response->callback_query->data) && $response->callback_query->data !== null) {
            return explode("|", $response->callback_query->data);
        }
        return false;
    }

    /**
     * @param string $callback_query
     * 
     * @return bool
     */
    public static function getCallback(string $callback_query): bool {
        $response = Updates::get();
        return (isset($response->callback_query->data) && $response->callback_query->data == $callback_query ? true : false);
    }

    /**
     * @param string $callback_query
     * 
     * @return bool
     */
    public static function getCallbackContains(string $callback_query): bool {
        $response = Updates::get();
        if(!empty($response->callback_query->data)) {
            return ($response->callback_query->data !== null && strpos($response->callback_query->data, $callback_query) !== false ? true : false);
        }
        return false;
    }

    /**
     * @param string $callback_initiator
     * @param string $pseudo_callback
     * 
     * @return bool
     */
    public static function getCallbackParam(string $callback_initiator, string $pseudo_callback): ?bool {

        $response = Updates::get();
        if(!empty($response->callback_query)) {
            $data = preg_split( "/\.|\|/", $response->callback_query->data);
            if(!empty($data[0]) && !empty($data[1])) {
                return ($data[0] == $callback_initiator && $data[1] == $pseudo_callback ? true : false);
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * @param array $params
     * 
     * @return string
     */
    public static function createCallbackParse(array $params = []): string {
        try {
            return http_build_query($params, '&');
        } catch(\Exception $e) {}
    }

    /**
     * @param string $data
     * 
     * @return array
     */
    public static function getCallbackParseData(string $data): array {
        parse_str($data, $params);
        return $params;
    }

    public static function callbackQueryReader(object &$output = null) {
        try {
            $updates = Updates::get();
            parse_str($updates->callback_query->data, $params);
        } finally {
            $output = (object) $params;
        }
        return $output; 
    }

    /**
	 * @param string $action
	 * @param array $value
	 * 
	 * @return array
	 */
	public static function createCallbackCommand(string $command, ?array $parameters = []): string {

        $content = array_merge($content = [
            'cmd' => $command
        ], $parameters);

        return http_build_query($content, '&');
    }

    public static function fromDB(string $parse_data, bool $from_db = true) {
        self::$parse_data = $parse_data;
        self::$from_db = $from_db;
        return new static;
    }

    public static function getCallbackCommand(int $command, $step = null): ?bool {

        if(!self::$from_db) {
            $response = Updates::get();
        } else {
            $response = '';
        }

        if(!empty($response->callback_query) || self::$from_db) {
            $parse_data = (self::$from_db ? self::$parse_data : $response->callback_query->data);
            parse_str($parse_data, $params);
            $params = (object) $params;

            if(!empty($params->cmd) && !empty($step) && !empty($params->step)) {
                return ((int) $params->cmd == $command && (int) $params->step == $step ? true : false);
            } elseif(!empty($params->cmd) && empty($step)) {
                return ((int) $params->cmd == $command ? true : false);
            }
        }
        return false;
    }

    /**
     * @param array $callback_commands
     * 
     * @return bool
     */
    public static function checkCallbackCommandInArray(array $callback_commands = []): bool {

        $response = Updates::get();

        if(!empty($response->callback_query) || empty($response->callback_query)) {

            if(!empty($response->callback_query)) {
                $parse_data = $response->callback_query->data;
            } else {
                $parse_data = \Libraries\Step::callback();
            }

            parse_str($parse_data, $params);
            $params = (object) $params;

            if(!empty($params->cmd)) {

                $callback_commands = array_map(function($available_updates) {
                    if(is_array($available_updates) && key_exists('callback', $available_updates)) {
                        return $available_updates['callback'];
                    } else {
                        return $available_updates;
                    }
                }, $callback_commands);


                return (in_array($params->cmd, $callback_commands) ? true : false);
            }
        }

        return false;

    }

    public static function getCommandFromCallback(): ?string {

        $response = Updates::get();

        if(!empty($response->callback_query) ||empty($response->callback_query)) {

            if(!empty($response->callback_query)) {
                $parse_data = $response->callback_query->data;
            } else {
                $parse_data = \Libraries\Step::callback();
            }
            
            parse_str($parse_data, $params);
            $params = (object) $params;

            if(!empty($params->cmd)) {
                return $params->cmd;
            }
        }
        return null;

    }

    public static function checkStringCommandInArray(?array $string_commands = []): bool {
        if(is_array($string_commands)) {
            $response = Updates::get();
            
            if(!empty($response->message->text)) {
                
                if(
                    !empty($response->message->entities[0]) && $response->message->entities[0]->type == 'bot_command' || 
                    !empty($response->message->entities[1]) && $response->message->entities[1]->type == 'bot_command'
                    ) {

                    $text = $response->message->text;

                    /**
                     * Check if the command is handling through the Bot name
                     */
                    if(contains(Updates::text(), config('telegram.bot_name')) || contains(Updates::text(), config('telegram.bot_name'))) {
                        $text = str_replace([
                            '@' . config('telegram.bot_name') . ' ',
                            '@' . config('telegram.bot_name') . ' '
                        ], ['', ''], $text);
                    }

                    $command = explode(' ', trim(strtolower($text)));
                    $command = str_replace('/', '', $command);
                    
                    return (in_array($command[0], $string_commands) ? true : false);
                } else {
                    return (in_array($response->message->text, $string_commands) ? true : false);
                }
            }
        }
        return false;
    }
}