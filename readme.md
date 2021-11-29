# Simple Componetized Telegram BOT

Imagine being able to create a bot with a few steps and each menu or function as if it were a method of a class. I have developed this tool to make it easier to create bots that require interaction between several screens.

# Project overview
### Prerequisites
```
- PHP >= 7.4
- Redis
- Mysql Mariadb
- Domain with SSL
```
### Features
- Components inside another components
- Easy component creation
- Easy step proccess

### How to use Components
In the event Events folder you can create a bunch of different loader or separated bots.

Events Folder Structure
```bash
Events
   └── example
       ├── main.php
       └── __query_helpers.php
```

To load this structure folder you have to define in the public folder
```php
$events = new EventLoader(Updates::userId(), Updates::language());
$events->setPath('example');
$events->forceEvents(['main.php', '__query_helpers.php']);
$events->getEvents();
```

In main.php you have to declare the Components route like this
```php
Component::group('Example', function ($component) {
    $component->include('initialPanels')->get([
        'start' => ['commands' => ['start', 'cancel']],
    ])->init();
});
```
- `start` refers to the static function inside the `initialPanels` folder that is inside the `Example` path
- `initialPanels` has to be named as `InitialPanelsComponent.php` 

The folder structure of the components is as follows and you can add as many as you like
```bash
Components
   ├── Example
   │   └── InitialPanelsComponent.php
   └── Component.php
```

### initialPanelsComponent.php overview
```php
<?php

namespace App\Components\Example;

use App\Models\Data;
use App\Components\Component;
use Telegram\{Client, Updates};

class InitialPanelsComponent extends Component {

    public static function start() {
        Client::sendMessage(Updates::userId(), "Example message");
    }

}
```

### How to > Telegram functions
Telegram updates are loaded globally in the Updates class, which you can use anywhere in static form

Simple sendMessage with inline keyboard
```php
$inline_keyboard = new InlineKeyboard;
$inline_keyboard->inlineKeyboardButton('Button name', 'callback_identifier');
$inline_keyboard->endRow();

Client::sendMessage(Updates::userId(), "Example message", [
    'reply_markup' => $inline_keyboard->inlineKeyboardMarkup()
]);
```

## License

MIT
