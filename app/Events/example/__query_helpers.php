<?php

use Telegram\{Client, Updates};

if(Client::getCallback('close_this')) {
    Client::deleteMessage(Updates::userId(), Updates::messageId());
}