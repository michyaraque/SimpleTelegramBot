<?php

namespace Libraries;

use Telegram\{Client, Updates};

class SystemControl {
    
    /**
     * @return void
     */
    public static function breakIsDemoIsActive() {

        if(env('MAINTENANCE_MODE') == true) {
            exit;
        }
    }

    /**
     * @return void
     */
    public static function getSystemStatsInChat(): void {
        $redis = new \App\Libraries\DBRedis;

        $memory = convert(memory_get_usage());
        $load_time = number_format(microtime(true) - BOT_START, 4) . " ms";
        $key = 'system_control:' . Updates::userId();

        $message = <<<MSG
        Memory consume: $memory
        Load Time: $load_time
        MSG;

        if(!$redis->exists($key)) {
            $get_id = Client::sendMessage(Updates::userId(), $message)->result->message_id;
            $redis->set($key, $get_id, 300);
        } else {
            $get_id = $redis->get($key);
            Client::editMessageText(Updates::userId(), $get_id, $message);
        }
    }

}