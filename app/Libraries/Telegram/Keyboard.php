<?php

namespace Telegram;

class Keyboard {

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
     * @return Keyboard
     */
    public function push($data): Keyboard {
		if(!is_array($data)){ 
            return false; 
        }
		$this->rows[] = $data;
		return $this;
    }

    /**
     * @return Keyboard
     */
    public function endRow(): Keyboard {
        $this->push($this->buttons);
        $this->buttons = [];
		return $this;
    }

    /**
     * @return Keyboard
     */
    public function shuffleButtons(): Keyboard {
       shuffle($this->rows);
       return $this; 
    }

    /**
     * @param mixed $text
     * @param mixed $data
     * @param string $type Optional. Type of button. Can be text, url, callback or switch.
     * 
     * @return Keyboard
     */
    public function keyboardButton($text): Keyboard {
        $this->buttons[] = ['text' => $text];
        return $this;  
	}

    /**
     * @param object $items
     * 
     * @return object
     */
    public function insertClosure(object $items): Keyboard {
        foreach($items->rows as $row) {
            $this->push($row);
        }
        return $this;
    }
	
    /**
     * @return string
     */
    public function keyboardMarkup(): string {
        $keyboard = ['keyboard' => $this->rows, 'resize_keyboard' => true];

        // Clean Keyboard
        unset($this->rows);
        unset($this->buttons);

        return json_encode($keyboard);
	}
}