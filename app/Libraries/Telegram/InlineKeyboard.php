<?php

namespace Telegram;

class InlineKeyboard {

    /**
     * @var array
     */
    public array $rows = [];
    
    /**
     * @var array
     */
    public array $buttons = [];
   
    /**
     * @param mixed $data
     * 
     * @return InlineKeyboard
     */
    public function push($data): InlineKeyboard {
		if(!is_array($data)){ 
            return false; 
        }
		$this->rows[] = $data;
		return $this;
    }

    /**
     * @return InlineKeyboard
     */
    public function endRow(): InlineKeyboard {
        $this->push($this->buttons);
        $this->buttons = [];
		return $this;
    }

    /**
     * @return InlineKeyboard
     */
    public function shuffleButtons(): InlineKeyboard {
       shuffle($this->rows);
       return $this; 
    }

    /**
     * @param mixed $text
     * @param mixed $data
     * @param string $type Optional. Type of button. Can be text, url, callback or switch.
     * 
     * @return InlineKeyboard
     */
    public function inlineKeyboardButton($text, $data, $type = "callback_data"): InlineKeyboard {
        $this->buttons[] = ['text' => $text, $type => $data];
        return $this;  
	}

    /**
     * @param object $items
     * 
     * @return object
     */
    public function insertClosure(object $items): InlineKeyboard {
        foreach($items->rows as $row) {
            $this->push($row);
        }
        return $this;
    }
	
    /**
     * @return string
     */
    public function inlineKeyboardMarkup(): string {
        $keyboard = ['inline_keyboard' => $this->rows];

        // Clean InlineKeyboard
        unset($this->rows);
        unset($this->buttons);

        return json_encode($keyboard);
	}
}