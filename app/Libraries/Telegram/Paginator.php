<?php

/**
 * Telegram inline keyboard paginator
 *
 * This class was made to paginate custom inline keyboards
 * Set extra buttons at bottom inline keyboard
 * Set limit per page
 *
 * @category   Class File
 * @author     Michael Araque <michyaraque@gmail.com>
 * @copyright  2021 Michael Araque
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * 
 * version 0.1 19/04/2021
 * Initial release
 */

namespace Telegram;

use Enums\CommandIdentifier;

class Paginator {

    private const LABELS = [
        'left' => '«',
        'right' => '»'
    ];

    private const PAGINATOR_NONE_CALLBACK_NEXT = 'next_page_none';
    private const PAGINATOR_NONE_CALLBACK_PREVIOUS = 'previous_page_none';

    /**
     * @var array
     */
    private $array_data = [];

    /**
     * @var int
     */
    private $data_counter;

    /**
     * @var string
     */
    private $callback_pointer;

    /**
     * @var string
     */
    private $callback_identifier;

    /**
     * @var string
     */
    private $button_text;

    /**
     * @var array
     */
    private $extra_button_callback_data;

    /**
     * @var array
     */
    private $extra_label_callback_data = [];

    /**
     * @var object
     */
    private $bottom_buttons;

    /**
     * @var int
     */
    public $offset;

    /**
     * @var int|null
     */
    public $limit;

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @param array|null $array_data
     */
    public function __construct(?int $offset = 0, ?int $limit = 10, ?array $array_data = []) {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->array_data = $array_data;
        $this->data_counter = count($this->array_data);
        $this->keyboard = new InlineKeyboard;
    }

    /**
     * @param array $callback_manager
     * 
     * @return array|null
     */
    private function createExtraLabelData(array $callback_manager = []): ?array {
        $extra_label_callback_counter = count($this->extra_label_callback_data);
        
        if(!empty($this->extra_label_callback_data)) {
            for($j = 0; $j < $extra_label_callback_counter; $j++) {
                $key = array_keys($this->extra_label_callback_data);
                $callback_manager[$key[$j]] = $this->extra_label_callback_data[$key[$j]];
            }
            return $callback_manager;
        }
    }

    /**
     * 
     * @return string
     */
    private function nextPage(): string {

        $callback_manager[CommandIdentifier::OFFSET] = $this->offset + $this->limit;
        $callback_manager[CommandIdentifier::LIMIT] = $this->limit;

        if(!empty($this->extra_label_callback_data)) {
            $callback_manager = $this->createExtraLabelData($callback_manager);
        }

        $next_page = (empty($this->array_data) || $this->data_counter < $this->limit ? 
            self::PAGINATOR_NONE_CALLBACK_NEXT : 
            Client::createCallbackCommand($this->callback_identifier, $callback_manager)
        );
        return $next_page;
    }

    /**
     * 
     * @return string
     */

    private function previousPage(): string {

        $callback_manager[CommandIdentifier::OFFSET] = $this->offset - $this->limit;
        $callback_manager[CommandIdentifier::LIMIT] = $this->limit;

        if(!empty($this->extra_label_callback_data)) {
            $callback_manager = $this->createExtraLabelData($callback_manager);
        }

        $previous_page = ($this->offset == 0 ? 
            self::PAGINATOR_NONE_CALLBACK_PREVIOUS : 
            Client::createCallbackCommand($this->callback_identifier, $callback_manager)
        );
        return $previous_page;
    }

    /**
     * @return array
     */
    public function generateButtons(): string {

        for($i = 0; $i < $this->data_counter; $i++) {

            $callback_manager[CommandIdentifier::OFFSET] = $this->offset;
            $callback_manager[CommandIdentifier::LIMIT] = $this->limit;

            $extra_callback_counter = count($this->extra_button_callback_data);
            if(!empty($this->extra_button_callback_data)) {
                for($j = 0; $j < $extra_callback_counter; $j++) {
                    $key = array_keys($this->extra_button_callback_data);
                    
                    if(!empty($this->array_data[$i][$this->extra_button_callback_data[$key[$j]]])) {
                        $callback_manager[$key[$j]] = $this->array_data[$i][$this->extra_button_callback_data[$key[$j]]];
                    } else {
                        $callback_manager[$key[$j]] = $this->extra_button_callback_data[$key[$j]];
                    }
                }
            }

            $callback_insert_data = Client::createCallbackCommand($this->callback_pointer, $callback_manager);

            $this->keyboard->inlineKeyboardButton($this->array_data[$i][$this->button_text], $callback_insert_data); 
            if ($i % 2 == 1 || $this->data_counter - 1 == $i && $i % 2 !== 1) {
                $this->keyboard->endRow();
            }
        }
        
        if($this->previousPage() !== self::PAGINATOR_NONE_CALLBACK_PREVIOUS) {
            $this->keyboard->inlineKeyboardButton(self::LABELS['left'], $this->previousPage());
        }
        
        if($this->nextPage() !== self::PAGINATOR_NONE_CALLBACK_NEXT) {
            $this->keyboard->inlineKeyboardButton(self::LABELS['right'], $this->nextPage());
        }
        
        $this->keyboard->endRow();

        if(!empty($this->bottom_buttons)) {
            $this->keyboard->push($this->bottom_buttons->buttons);
        }
      
        return $this->keyboard->inlineKeyboardMarkup();
    }

    /**
     * @param string $value
     * 
     * @return Paginator
     */
    public function setCallbackPointer(string $value): Paginator {
        $this->callback_pointer = $value;
        return $this;
    }

    /**
     * @param string $value
     * 
     * @return Paginator
     */
    public function setCallbackIdentifier(string $value): Paginator {
        $this->callback_identifier = $value;
        return $this;
    }

    /**
     * @param string $value
     * 
     * @return Paginator
     */
    public function setButtonText(string $value): Paginator {
        $this->button_text = $value;
        return $this;
    }

    /**
     * @param array $data
     * 
     * @return Paginator
     */
    public function setExtraButtonCallbackData(array $data): Paginator {
        $this->extra_button_callback_data = $data;
        return $this;
    }

    /**
     * @param array $data
     * 
     * @return Paginator
     */
    public function setExtraLabelsCallbackData(array $data): Paginator {
        $this->extra_label_callback_data = $data;
        return $this;
    }


    /**
     * @param callable|null $func
     * 
     * @return Paginator
     */
    public function setBottomButtons(callable $func = null): Paginator {
        $this->bottom_buttons = $func();
        return $this;
    }

    /**
     * @return string
     */
    public function getPaginatorKeyboard(): string {
        return $this->generateButtons();
    }

}