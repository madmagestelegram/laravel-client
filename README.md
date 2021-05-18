# Telegram + Laravel

Laravel + telegram integration. Based on [telegram bot types](https://github.com/madmagestelegram/Types)

# Install
`composer require madmagestelegram/laravel`

# Concepts
### Main concept
The main concept is creating handlers for incoming updates from telegram.

It should implement [abstract handler](https://github.com/madmagestelegram/laravel-client/blob/master/src/Handler/AbstractHandler.php)

### Examples of handlers
There is one build in [handler for telegram commands](https://github.com/madmagestelegram/laravel-client/blob/master/src/Handler/Command/CommandHandler.php)

### Service provider
To define custom handlers or telegram-command-handler, it necessary to create and register own service provider and extend it from `\MadmagesTelegram\Laravel\HandlerServiceProvider` 
```php
<?php 
declare(strict_types=1);

namespace App\Providers;

class TelegramServiceProvider extends \MadmagesTelegram\Laravel\HandlerServiceProvider
{

    /** @var array Handlers for telegram commands */
    protected array $telegramCommands = [];

    // Other handlers
    protected array $messageHandlers = [
        // This handler is for telegram-commands,
        // i.e. it makes $this->telegramCommands working
        \MadmagesTelegram\Laravel\Handler\Command\CommandHandler::class
    ];
    protected array $editMessageHandlers = [];
    protected array $channelPostHandlers = [];
    protected array $editedChannelPostHandlers = [];
    protected array $inlineQueryHandlers = [];
    protected array $chosenInlineResultHandlers = [];
    protected array $callbackQueryHandlers = [];
    protected array $preCheckoutQueryHandlers = [];
    protected array $shippingQueryHandlers = [];
    protected array $pollAnswerHandlers = [];
    protected array $pollHandlers = [];
}

```

### Command handler
The command handler built for handling telegram commands i.e. **/start** for example.

All commands should implement [AbstractCommand](https://github.com/madmagestelegram/laravel-client/blob/master/src/Handler/Command/AbstractCommand.php)
and should be defined in `\MadmagesTelegram\Laravel\HandlerServiceProvider::$telegramCommands` of own service provider

```php
<?php declare(strict_types=1);

namespace App\Module\Telegram\Command;

use MadmagesTelegram\Laravel\Handler\Command\AbstractCommand;
use MadmagesTelegram\Laravel\WebhookClient;
use MadmagesTelegram\Types\Type\Message;
use Illuminate\Http\JsonResponse;

class Start extends AbstractCommand
{

    private WebhookClient $client;

    public function __construct(WebhookClient $client)
    {
        // It possible to response, during webhook request 
        // https://core.telegram.org/bots/api#making-requests-when-getting-updates
        $this->client = $client;
    }
    
     public function getName(): string
    {
        return 'start';
    }

    public function execute(Message $message, bool $isPrivate): ?JsonResponse
    {
        return $this->client->sendMessage($message->getChat()->getId(), 'Hello there !');
    }
}
```

and register it 
```php
<?php 
declare(strict_types=1);

namespace App\Providers;

class TelegramServiceProvider extends \MadmagesTelegram\Laravel\HandlerServiceProvider
{
    protected array $telegramCommands = [
        \App\Module\Telegram\Command\Start::class
    ];
}

```

# Laravel commands
`madmagestelegram:registerWebhook` - register webhook in telegram. It makes all handlers to work