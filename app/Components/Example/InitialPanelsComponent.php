<?php

namespace App\Components\Example;

use App\Models\Data;
use App\Components\Component;
use Telegram\{Client, Updates};

class InitialPanelsComponent extends Component {

    public static function start() {

        if(Client::getCommand('start') && Updates::isMessageFromPrivateChat()) {
            if(empty(Data::isRegister(Updates::userId()))) {
                Data::registerUser(Updates::userId(), Updates::username() ?? null, Updates::firstName(), Updates::language() ?? 'es', 'editor');
            }
            Data::resetTempAndStep(Updates::userId());
        } 
    
        if (Client::getCommand('cancel')) {
            Data::resetTempAndStep(Updates::userId());
        }

        Client::sendMessage(Updates::userId(), "Example message");


    }
  
}