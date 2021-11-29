<?php

/**
 * Component Class
 *
 * Class made to interact directly with the Components path in the directory
 * you can handle all request of the chatbot directly with components
 *
 * @category   Library
 * @author     Michael Araque <michyaraque@gmail.com>
 * @copyright  2021 Michael Araque
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * 
 * version 1.0.0 26/07/2021
 * Initial release
 */

namespace App\Components;

use Telegram\{Client, Updates};

class Component {

    const GLOBAL = 'global';

    /**
     * @var string
     */
    private static $group_alias = '';
    
    /**
     * @var string
     */
    private static $subgroup_alias = '';

    /**
     * @var array
     */
    private array $chaining;

    /**
     * @var bool
     */
    private static $stop_execution = false;

    /**
     *  
     * @param string $value
     * 
     * @return void
     */
    public function include(string $value): Component {
        $this->chaining['value'] = $value;
        return $this;
    }

    /**
     * @method static \App\Component\Component get(array|string|callable|null $methods = null)
     * @param array $methods
     * 
     * @return Component
     */
    public function get(array $methods): Component {
        
        $this->chaining['methods'] = $methods;
        return $this;
    }

    /**
     * @param string $group_alias
     * @param callable|null $callback
     * 
     * @return callable
     */
    public static function group(string $group_alias = '', callable $callback = null) {

        if(!empty($group_alias)) {
            self::$group_alias = $group_alias;
        }

        if ($callback) {  
            $callback(new Component);
        }
    }

    /**
     * @method static \App\Component\Component subGroup(string $subgroup_alias, array|string|callable|null $callback = null)
     * @param string $group_alias
     * @param callable|null $callback
     * 
     * @return void
     */
    public static function subGroup(string $subgroup_alias = '', callable $callback = null): void {

        if(!empty($subgroup_alias)) {
            self::$subgroup_alias = '\\' . $subgroup_alias;
        }

        if ($callback) {  
            $callback(new Component);
        }

    }

    /**
     * @param array $array
     * @param mixed $needle
     * 
     * @return array|null
     */
    public function getInitCommandArray(array $array, $needle): ?array {
        $iterator = new \RecursiveArrayIterator($array);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
        $content = [];
        foreach ($recursive as $key => $value) {
          if ($key === $needle) {
            $content[] = ['method' => $iterator->key(), 'commands' => $value];
          }
        } 
        return $content;
    }

    /**
     * @param string $class
     * @param string $method
     * 
     * @return void
     */
    private function retrieveMethod(string $class, string $method, ?int $update = null): void {

        if(!self::$stop_execution) {
            try {

                if(class_exists($class)) {
                    if(method_exists($class, $method)) {
       
                        /*
                        |--------------------------------------------------------------------------
                        | Load methods with Accepted Updates array
                        |--------------------------------------------------------------------------
                        |
                        | This section will load the method if the method is in the Accepted Updates array
                        | setted in the acceptedUpdates method.
                        |
                        */
                        
                        if(!empty($update)){
                            if(is_array($this->chaining['methods'][$method])) {
                                $method_updated = $this->chaining['methods'][$method]['callback'];
                            } else {
                                $method_updated = $this->chaining['methods'][$method];
                            }
                            
                            if($method_updated === $update) {
                                call_user_func("${class}::${method}");
                                unset($this->chaining['methods']);
                            }
                        }
                        
                        /*
                        |--------------------------------------------------------------------------
                        | Load methods without updates in his array
                        |--------------------------------------------------------------------------
                        |
                        | This section allows to load methods without the need of the Accepted Updates
                        |
                        */

                        if(empty($update)) {
                            call_user_func("${class}::${method}");
                        }

                    } else {
                        throw new \Exception(sprintf('Method %s does not exist in class %s', $method, $class));
                    }
                } else {
                    throw new \Exception(sprintf('Class %s does not exist', $class));
                }
  
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * 
     * @param Component $instance
     * @param mixed $class
     * 
     * @return void
     */
    private function initSingleCommands(Component $instance, $class): void {
        
        if(!empty($instance->chaining['methods']) && in_array(self::GLOBAL, array_values($instance->chaining['methods']))) {
            foreach($instance->chaining['methods'] as $method => $key) {
                if(!is_array($method)) {
                    $instance->retrieveMethod($class, $method, null);
                }
            }
        }
    }

    /**
     * Handle commands received from text chat
     * 
     * @param Component $instance
     * @param string $class
     * 
     * @return void
     */
    private function initTextCommands(Component $instance, string $class): void {
        
        if(
            !empty($instance->chaining['methods']) && 
            is_array($instance->chaining['methods']) && 
            !empty(Updates::text())
            ){
            $commands = $this->getInitCommandArray($instance->chaining['methods'], 'commands');
            if(!empty($commands)) { 
                
                foreach($commands as $command) {
                    if(Client::checkStringCommandInArray($command['commands'])) {
                        $method = $command['method'];
                        
                        if(!empty($instance->chaining['methods'][$method]['callback'])) {
                            $update = $instance->chaining['methods'][$method]['callback'];
                        } else {
                            $update = null;
                        }
                        $instance->retrieveMethod($class, $method, $update);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Handle commands received from Inline Keyboard or even Database conversation flow
     * 
     * @param Component $instance
     * @param string $class
     * 
     * @return void
     */
    private function initCallbackCommands(Component $instance, string $class): void {
       
        if(is_array($instance->chaining['methods']) && 
            Client::checkCallbackCommandInArray(array_values($instance->chaining['methods']))) {

            $callback_command = Client::getCommandFromCallback();
            $method = array_search($callback_command, array_map(function($var) {
                if(is_array($var) && key_exists('callback', $var)) {
                    return $var['callback'];
                } else {
                    return $var;
                }
            }, $instance->chaining['methods']));

            $update = $instance->chaining['methods'][$method];
            
            if(is_array($update)) {
                $update = $update['callback'];
            }

            $instance->retrieveMethod($class, $method, $update);
        }
    }

    /**
     * Init the chaining method and get the result.
     *
     * @return void
     */
    public function init(): void {

        $value = ucfirst($this->chaining['value']) . 'Component';
        
        $class = '\\App\\Components\\' . self::$group_alias . self::$subgroup_alias. '\\' . $value;

        if(isset($this->chaining['methods']) && is_array($this->chaining['methods'])) {
           
            $this->initSingleCommands($this, $class);
            $this->initCallbackCommands($this, $class);
            $this->initTextCommands($this, $class);

            unset($this->chaining['methods']);

        }
    }

    /**
     * @return void
     */
    public static function stop(): void {
        self::$stop_execution = true;
    }

    /**
     * @param string $class
     * @param string $method
     * 
     * @return void
     */
    public static function bring(string $class, string $method): void {
        $class = str_replace('/', '\\', $class);
        $class = $class . 'Component';
        $class = '\\App\\Components\\' . $class;
        if(class_exists($class)) {
            if(method_exists($class, $method)) {
                call_user_func("${class}::${method}");
            }
        }
    }
}