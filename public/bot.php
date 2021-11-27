<?php
error_reporting(E_ALL);
use Libraries\EventLoader;
use App\Libraries\Model as Database;
use Telegram\{Client as TelegramClient, Updates};

define('BOT_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = \Dotenv\Dotenv::createUnsafeImmutable(dirname(__FILE__, 2));
$dotenv->load();

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| When the telegram api webhook makes a request, this page is loaded
| through the webhook classes.
|
*/
Database::create();
TelegramClient::create(env('TELEGRAM_API_KEY'));

$events = new EventLoader(Updates::userId(), Updates::language());
$events->setPath('example');
$events->forceEvents(['main.php', '__query_helper.php']);
$events->getEvents();