<?php

if (!function_exists('value')) {
    /**
     * [Larvel Helpers]
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value) {
        return $value instanceof Closure ? $value() : $value;
    }
}

/**
 * [Larvel Helpers]
 * Determine if a given string starts with a given substring.
 *
 * @param  string  $haystack
 * @param  string|string[]  $needles
 * @return bool
 */
function startsWith($haystack, $needles) {
    foreach ((array) $needles as $needle) {
        if ((string) $needle !== '' && str_starts_with($haystack, $needle)) {
            return true;
        }
    }

    return false;
}

/**
 * [Larvel Helpers]
 * Determine if a given string ends with a given substring.
 *
 * @param  string  $haystack
 * @param  string|string[]  $needles
 * @return bool
 */
function endsWith($haystack, $needles) {
    foreach ((array) $needles as $needle) {
        if (
            $needle !== '' && $needle !== null
            && str_ends_with($haystack, $needle)
        ) {
            return true;
        }
    }

    return false;
}

/**
 * [Larvel Helpers]
 * Gets the value of an environment variable. Supports boolean, empty and null.
 *
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);

    if ($value === false) {
        return value($default);
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;

        case 'false':
        case '(false)':
            return false;

        case 'empty':
        case '(empty)':
            return '';

        case 'null':
        case '(null)':
            return;
    }

    if (startsWith($value, '"') && endsWith($value, '"')) {
        return substr($value, 1, -1);
    }

    return $value;
}

/**
 * Language helper function
 * 
 * @param string $value
 * @return string|null
 */
function translate(string $value = '', ?array $binder = []) {
    $lang_data = Libraries\Lang::get($value, $binder);
    if(is_array($lang_data)) {
        return '{' . $value . '}';
    }
    return $lang_data;
}

/**
 * @param string $value
 */
function config(string $value) {
    return Libraries\Config::get($value);
}

function getUrlFromText(string $string = '') {
    $regex = '/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/i';
    preg_match_all($regex, $string, $matches);
    return ($matches[0]);
}

function getUnshortedLink($url){
    $headers = @get_headers($url, 1);
    
    if(!empty($headers['Location'])){
        $header = $headers['Location'];
    }
    if(!empty($header) && !is_array($header)) {
        return $header;
    } elseif(!empty($header) && is_array($header)) {
        return $header[1];
    } else {
        $headers = json_encode(@get_headers($url));
        if(!empty(preg_match('/Location:\s(?<site>[^"]+)/', $headers, $match))) {
            $unshort_url = str_replace('\/', '/', $match['site']);
            return $unshort_url;
        }
    }
}

function getTelegramChatInstance(?int $user_id, ?string $name) {
    try {
        if(!empty($user_id) && !empty($name)) {
            return sprintf("<a href=\"tg://user?id=%u\">%s</a>", $user_id, $name);
        } else {
            throw new \Exception("Error getting some value", 1);  
            die;
        }
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    
}

/**
 * @return string
 */
function timeToNextDay(): string {
    $date1 = new \DateTime('NOW');
    $date2 = new \DateTime();
    $date1 = $date1->format("Y-m-d H:i:s");
    $date2 = $date2->modify('+1 day')->format('Y-m-d 00:00:00');
    return strtotime($date2) - strtotime($date1);
}

/**
 * @param string $string
 * 
 * @return int
 */
function checkHtmlString(string $string): int {
    $start_string = strpos($string, '<');
    $end_string = strrpos($string, '>', $start_string);
    if ($end_string !== false) {
        $string = substr($string, $start_string);
    } else {
        $string = substr($string, $start_string, strlen($string) - $start_string);
    }
    $string = "<div>$string</div>";
    libxml_use_internal_errors(true);
    libxml_clear_errors();
    simplexml_load_string($string);
    $ret = (count(libxml_get_errors()) == 0 ? true : false);
    return $ret;
}

function getParsedTimeLeft($time_left) {
    return gmdate("H", $time_left).":".gmdate("i", $time_left).":".gmdate("s", $time_left);
}

/**
 * @param string $string
 * @param string $find_value
 * 
 * @return bool
 */
function contains(string $string, string $find_value): bool {
    return (strpos($string, $find_value) !== false ? true : false);
}

/**
 * @return int
 */
function getUniqueId(): int {
    return md5($_SERVER['REQUEST_TIME'] + mt_rand(1000,9999));
}

/**
 * @param mixed $size
 * 
 * @return string
 */
function convert($size): string {
    $unit = ['b','kb','mb','gb','tb','pb'];
    return @round($size / pow(1024, ($i=floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}