<?php

namespace Libraries;

use App\Models\Data;

class EventLoader {

    /**
     * @var array
     */
    public $initial_events;

    /**
     * @var string
     */
    private string $user_id;

    /**
     * @var string|null
     */
    protected ?string $language;
    
    /**
     * @var string
     */
    protected string $fallback_language = 'es';

    /**
     * @param int $user_id
     * @param string|null $language
     */
    public function __construct(int $user_id, ?string $language) {
        
        if(empty($user_id)) {
            throw new \Exception('User is not setted');
        }

        $this->user_id = $user_id;
        $this->event_path = dirname(__FILE__, 3) . '/app/Events/';
        $this->getDatabaseLanguage();
        $this->getDatabaseSteps();
        $this->setEventLang($language);
    }

    /**
     * @param string $folder
     * 
     * @return EventLoader
     */
    public function setPath(string $folder): EventLoader {
        $this->event_path = $this->event_path . $folder . '/';
        return $this;
    }

    /**
     * @return EventLoader
     */
    protected function getDatabaseLanguage(): EventLoader {
        $data = Data::getLanguage($this->user_id);
        if(!empty($data)) {
            $this->language = $data['language'] ?? null;
        } else {
            $this->language = null;
        }
        return $this;
    }

    /**
     * @return EventLoader
     */
    protected function getDatabaseSteps(): ?EventLoader {
        $data = Data::getAllSteps($this->user_id);
        
        if(!$data)
            return null;

        \Libraries\Step::set($data['step'], 'step');
        return $this;
    }
    
    /**
     * @param string $dir
     * @param mixed $first_files
     * @param array $out
     * 
     * @return array
     */
    public function loadEvents(string $dir, $first_files, &$out = []) {
        $directory_scan = scandir($dir);

        $file = '';
        
        $file_path = function($dir, $filename) {
            return realpath($dir . DIRECTORY_SEPARATOR . $filename);
        };
    
        if(!empty($first_files)) {
            foreach($first_files as $filename) {
                $out[] = $this->event_path . $filename;
            }
        }
    
        foreach ($directory_scan as $filename) {
            $file_resource = $file_path($dir, $filename);
            if (!is_dir($file_resource)) {
                $out[] = $file_resource;
            } else if ($filename != "." && $filename != "..") {
                $this->loadEvents($file_resource, '', $out);
   
                if(substr($file, -4) != ".php"){ continue; }
    
                $out[] = $file_resource;
            }
        }
        return array_unique($out);
    }

    /**
     * @param array $initial_events
     * 
     * @return void
     */
    public function forceEvents(array $initial_events = []): EventLoader {
        $this->initial_events = $initial_events ?? [];
        return $this;
    }

    /**
     * @param string $language_code
     * 
     * @return void
     */
    public function setEventLang(?string $language_code): void {

        $database_lang = $this->language;
        $language = function() use ($language_code, $database_lang) {
            if(!empty($database_lang)) {
                return $database_lang;
            } elseif(!empty($language_code)) {
                return $language_code;
            } else {
                return $this->fallback_language;
            }  
        };
        \Libraries\Lang::set($language());
    }

    /**
     * @return array
     */
    public function getEvents(): void {
        $file_path = $this->loadEvents($this->event_path, $this->initial_events);

        foreach($file_path as $file) {
            require_once $file;
        }
    }
    
}