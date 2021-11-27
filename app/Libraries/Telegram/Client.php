<?php

/**
 * Telegram Api Wrapper
 *
 * This class was made to interact with the Telegram Api
 * you can use and interact with most of the methods of the
 * Telegram API.
 *
 * @category   Library
 * @author     Michael Araque <michyaraque@gmail.com>
 * @copyright  2021 Michael Araque
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @since      03/06/2018
 * 
 * version 1.1.9 19/04/2021
 * Stable release
 */

namespace Telegram;

use Libraries\Logger;

use Telegram\{Updates, UpdateObject};
use Telegram\Traits\{
    Tools,
    KeyboardUtils
};

class Client {

    use Tools, KeyboardUtils;

    /**
     * Api url
     * 
     * @var string
     */
    const API_URL = 'https://api.telegram.org/bot';

    /**
     * @var object
     */
    static public object $updates;
    /**
     * @var string
     */
    static private string $api_key;

    /**
     * @var string
     */
    static private string $token;

    /**
     * @var string
     */
    static public string $parse_mode;

    /**
     * @param string|null $api_key
     * @param string $parse_mode
     * 
     * @return self
     */
    public static function create(string $api_key = null, string $parse_mode = 'HTML') {
        self::$api_key = $api_key;
        self::$parse_mode = $parse_mode;
        self::getUpdates();
    }

    /**
     * @param string $method
     * @param null $params
     * 
     * @return string
     */
    public static function request(string $method, $params = null): ?object {
        $url = self::API_URL . self::$api_key . "/" . $method . "?" . http_build_query($params); 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
            ]
        );
        $output_from_request = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        if(env('TELEGRAM_LOG_TRACE')) {
            Logger::setLog('output', $output_from_request);
        }

        $output_decode = json_decode($output_from_request);

        if(property_exists($output_decode, 'ok') && $output_decode->ok == false) {
            error_log('[' . $url . ']: ' . $output_from_request);
        }

        return $output_decode;
    }
 
    /**
     * @return object
     */
    public static function getUpdates() {
        
        $content = file_get_contents('php://input');
        if(!empty($content)) {
            $update = json_decode($content);
            
            if(env('TELEGRAM_LOG_TRACE') && !empty($update)) {
                Logger::setLog('input', $content);
            }
    
            Updates::set($update);
        }
    }
   
    /**
     * @param string $token
     * 
     * @return void
     */
    public static function setToken(string $token): void {
        self::$token = $token;
    }

    /**
     * @param string $url
     * 
     * @return void
     */
    public static function setWebhook(string $url): object {
        return self::request(__FUNCTION__, [
            'url' => $url
        ]);
    }
    
    public static function deleteWebhook(bool $drop_updates = true): object {
        return self::request(__FUNCTION__, [
            'drop_pending_updates' => $drop_updates
        ]);
    }

    /**
     * @return string
     */
    public static function getWebhookInfo(): object {
        return self::request(__FUNCTION__, []);
    }

    /**
     * @return string
     */
    public static function getMe(): object {
        return self::request(__FUNCTION__, []);
    }

    // Metodos

    /**
     * @param int $chat_id
     * @param int $user_id
     * 
     * @return object
     */
    public static function getChatMember(int $chat_id, int $user_id): object {

        return self::request(__FUNCTION__, [
            'chat_id'   => $chat_id,
            'user_id'   => $user_id
        ]);

    }

    /**
     * @param int $chat_id
     * 
     * @return object
     */
    public static function getChat(int $chat_id): object {

        return self::request(__FUNCTION__, [
            'chat_id'   => $chat_id
        ]);

    }

    /**
     * @param int $chat_id
     * @param string $text
     * @param array $parameters
     * 
     * @return object
     */
    public static function sendMessage(int $chat_id, string $text, array $parameters = []) {

        $parameters = array_merge([
            'chat_id'                       => $chat_id,
            'parse_mode'                    => self::$parse_mode,
            'text'                          => $text,
            'disable_notification'          => true,
            'disable_web_page_preview'      => true
        ], $parameters);

        return self::request(__FUNCTION__, $parameters);
    }

    /**
     * @param string $chat_id
     * @param int $messageid
     * 
     * @return void
     */
    public static function deleteMessage(string $chat_id, $messageid): object {
        if (is_array($messageid)) {
            $counter = count($messageid);
            for($i = 0; $i <= $counter - 1; $i++) {
                self::request(__FUNCTION__, [
                    'chat_id'       => $chat_id,
                    'message_id'    => $messageid[$i]
                ]);
            }
        } else {
            self::request(__FUNCTION__, [
                'chat_id'           => $chat_id,
                'message_id'        => $messageid
            ]);
        }
    }

    /**
     * @param string $chat_id
     * @param int $messageid
     * @param string $newtext
     * 
     * @return string
     */
    public static function editMessageText(string $chat_id, int $message_id, string $text, array $parameters = []): object {

        $parameters = array_merge([
            'chat_id'                       => $chat_id,
            'message_id'                    => $message_id,
            'text'                          => $text,
            'parse_mode'                    => self::$parse_mode,
            'disable_web_page_preview'      => true,
            'disable_notification'          => true
        ], $parameters);

        if(!empty($parameters['reply_markup'])) {
            self::answerCallbackQuery(Updates::callbackId(), '');
        }

        return self::request(__FUNCTION__, $parameters);
    }

    /**
     * @param string $chat_id
     * @param int $messageid
     * @param string $caption
     * @param string|null $keyboard
     * 
     * @return string
     */
    public static function editMessageCaption(string $chat_id, int $messageid, string $caption, array $parameters = []): object {

        $parameters = array_merge([
            'chat_id'                       => $chat_id,
            'message_id'                    => $messageid,
            'parse_mode'                    => self::$parse_mode,
            'caption'                       => $caption
        ], $parameters);
        
        return self::request(__FUNCTION__, $parameters);
    }

    public static function inputMediaPhoto(array $parameters = []) {

        $parameters = array_merge([
            'type' => 'photo',
            'parse_mode' => self::$parse_mode
        ], $parameters);

        return json_encode($parameters);
    }
      
    /**
     * @param string $chat_id
     * @param int $messageid
     * @param string $media
     * @param string|null $keyboard
     * 
     * @return string
     */
    public static function editMessageMedia(string $chat_id, int $messageid, array $parameters = []): object {

        $parameters = array_merge([
            'chat_id'                   => $chat_id,
            'message_id'                => $messageid
        ], $parameters);

        if(!empty($parameters['reply_markup'])) {
            self::answerCallbackQuery(Updates::callbackId(), '');
        }

        return self::request(__FUNCTION__, $parameters);
    }

    /**
     * @param string $chat_id
     * @param int $messageid
     * @param string $keyboard
     * 
     * @return string
     */
    public static function editMessageReplyMarkup(string $chat_id, int $message_id, array $parameters = []): object {

        $parameters = array_merge([
            'chat_id'               => $chat_id,
            'message_id'            => $message_id,
        ], $parameters);

        return self::request(__FUNCTION__, $parameters);
    }

    /**
     * @param string $chat_id
     * @param string $photo
     * @param string|null $text
     * @param string|null $keyboard
     * 
     * @return string
     */
    public static function sendPhoto(string $chat_id, string $photo, ?string $text = null, array $parameters = []): object {

        $parameters = array_merge([
            'chat_id'       => $chat_id,
            'photo'         => $photo,
            'caption'       => $text,
            'parse_mode'    => self::$parse_mode,
            'disable_notification' => true
        ], $parameters);

        return self::request(__FUNCTION__, $parameters);
    }

    
    /**
     * @param string $chat_id
     * @param string $action
     * 
     * @return string
     */
    public static function sendChatAction(string $chat_id, string $action = 'typing'): object {

        return self::request(__FUNCTION__, [
            'chat_id'   => $chat_id,
            'action'    => $action
        ]);
    }

    /**
     * @param int $callback_id
     * @param string $text
     * @param bool $alert
     * 
     * @return string
     */
    public static function answerCallbackQuery(int $callback_id, ?string $text, bool $alert = false): object {
        
        return self::request(__FUNCTION__, [
            'callback_query_id'     => $callback_id,
            'text'                  => $text,
            'show_alert'            => $alert
        ]);
    }

    /**
     * @param int $inline_id
     * @param string $results
     * @param int|null $cache
     * @param string|null $sw_text
     * @param string|null $sw_parameter
     * 
     * @return string
     */
    public static function answerInlineQuery(int $inline_id, string $results, ?int $cache = 0, ?string $sw_text = null, ?string $sw_parameter = null): object {

        return self::request(__FUNCTION__, [
            'inline_query_id'       => $inline_id,
            'results'               => $results,
            'cache_time'            => $cache,
            'switch_pm_text'        => $sw_text,
            'switch_pm_parameter'   => $sw_parameter
        ]);
    }

    /**
     * @param string $chat_id
     * @param string $document
     * @param string $text
     * 
     * @return string
     */
    public static function sendDocument(string $chat_id, string $document, string $text): object {

        return self::request(__FUNCTION__, [
            'chat_id'       => $chat_id,
            'document'      => $document,
            'caption'       => $text,
            'parse_mode'    => self::$parse_mode
        ]);
    }
    
    /**
     * @param int $file_id
     * 
     * @return string
     */
    public static function getFile(string $file_id): string {

        $file_path = self::request(__FUNCTION__, [
            'file_id'    => $file_id
        ]);
        return $file_path->result->file_path;
    }

    /**
     * @param string $file
     * @param string $name
     * 
     * @return string
     */
    public static function getFileDownload(string $file, string $name): string {
        $file_path = 'storage/images';
        $file_name = $name . '_' . bin2hex(random_bytes(16)) . '.jpg';
        $url = 'https://api.telegram.org/file/bot' . self::$api_key . '/' . $file;
        file_put_contents(realpath(dirname(__FILE__, 4)) . '/' . $file_path . '/' . $file_name, file_get_contents($url));
        return $file_path . '/' . $file_name;
    }

    /**
     * @param int $chat_id
     * @param int $from_chat_id
     * @param int $message_id
     * 
     * @return string
     */
    public static function forwardMessage(int $chat_id, int $from_chat_id, int $message_id): object {

        return self::request(__FUNCTION__, [
            'chat_id'       => $chat_id,
            'from_chat_id'  => $from_chat_id,
            'message_id'    => $message_id,
            'disable_notification'    => true
        ]);
    }

    /**
     * @return string
     */
    public static function forceReply(): string {
        return json_encode(['force_reply' => true, 'selective' => true]);
    }
    
    /**
     * @return self
     */
    public static function tools(): self {
        return new self;
    }
}