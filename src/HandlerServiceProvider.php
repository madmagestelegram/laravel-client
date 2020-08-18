<?php declare(strict_types = 1);

namespace MadmagesTelegram\Laravel;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use MadmagesTelegram\Laravel\Handler\Command\CommandHandler;

class HandlerServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public const SERVICE_HANDLERS            = 'maddmagestelegram.handlers';
    public const SERVICE_DEFAULT_HANDLERS    = 'maddmagestelegram.default_handlers';
    public const SERVICE_MIDDLEWARE_HANDLERS = 'maddmagestelegram.middleware_handlers';
    public const SERVICE_COMMANDS            = 'maddmagestelegram.commands';

    protected $messageHandlers = [
        CommandHandler::class,
    ];
    protected $editMessageHandlers = [];
    protected $channelPostHandlers = [];
    protected $editedChannelPostHandlers = [];
    protected $inlineQueryHandlers = [];
    protected $chosenInlineResultHandlers = [];
    protected $callbackQueryHandlers = [];
    protected $preCheckoutQueryHandlers = [];
    protected $shippingQueryHandlers = [];
    protected $pollAnswerHandlers = [];
    protected $pollHandlers = [];
    protected $defaultHandlers = [];

    protected $telegramCommands = [];
    protected $middlewareHandlers = [];

    public function boot(Router $router): void
    {
        $routeKey = ServiceProvider::CONFIG_FILE . '.route';
        $router->post(config($routeKey))->middleware(HandlerMiddleware::class)->name($routeKey);
    }

    public function register(): void
    {
        $this->app->singleton(
            self::SERVICE_HANDLERS,
            function () {
                return [
                    'message'              => $this->messageHandlers,
                    'edited_message'       => $this->editMessageHandlers,
                    'channel_post'         => $this->channelPostHandlers,
                    'edited_channel_post'  => $this->editedChannelPostHandlers,
                    'inline_query'         => $this->inlineQueryHandlers,
                    'chosen_inline_result' => $this->chosenInlineResultHandlers,
                    'callback_query'       => $this->callbackQueryHandlers,
                    'shipping_query'       => $this->shippingQueryHandlers,
                    'pre_checkout_query'   => $this->preCheckoutQueryHandlers,
                    'poll'                 => $this->pollHandlers,
                    'poll_answer'          => $this->pollAnswerHandlers,
                ];
            }
        );
        $this->app->singleton(
            self::SERVICE_DEFAULT_HANDLERS,
            function () {
                return $this->defaultHandlers;
            }
        );
        $this->app->singleton(
            self::SERVICE_MIDDLEWARE_HANDLERS,
            function () {
                return $this->middlewareHandlers;
            }
        );
        $this->app->singleton(
            self::SERVICE_COMMANDS,
            function (Application $app) {
                $commands = [];
                foreach ($this->telegramCommands as $commandClass) {
                    $commands[] = $app->make($commandClass);
                }

                return $commands;
            }
        );
    }
}
