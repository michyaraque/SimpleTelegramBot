<?php

use App\Components\Component;

Component::group('Example', function ($component) {

    $component->include('initialPanels')->get([
        'start' => ['commands' => ['start', 'cancel']],
    ])->init();

});

if(env('TELEGRAM_PERFORMANCE_TRACE')) {
    \Libraries\SystemControl::getSystemStatsInChat();
}
