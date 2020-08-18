<?php declare(strict_types = 1);

return [
    'token'          => env('MADMAGES_TELEGRAM_TOKEN'),
    'guzzle_configs' => [],
    'route'          => '/app/' . env('MADMAGES_TELEGRAM_TOKEN') . '/webhook',
    'bot_name'       => env('MADMAGES_TELEGRAM_BOT_NAME'),
    'webhook_host'   => null,
];
