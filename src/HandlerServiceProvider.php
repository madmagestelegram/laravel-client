<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use MadmagesTelegram\Laravel\Handler\Command\CommandHandler;

class HandlerServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Handlers of income update
     */

    public const
        /** @var string Priority #1 */
        SERVICE_MIDDLEWARE_HANDLERS = 'maddmagestelegram.middleware_handlers',
        /** @var string Priority #2 */
        SERVICE_SPECIFIC_UPDATE_HANDLERS = 'maddmagestelegram.specific_update_handlers',
        /** @var string Priority #3 */
        SERVICE_DEFAULT_HANDLERS = 'maddmagestelegram.default_handlers';

    /** @var string Commands for CommandHandler */
    public const SERVICE_COMMAND_HANDLER_COMMANDS = 'maddmagestelegram.command_handler_commands';

    // Specific handlers
    protected array $messageHandlers = [
        CommandHandler::class,
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

    // Non specific handlers, i.e. handler for any update income
    protected array $defaultHandlers = [];

    // Commands for CommandHandler::class
    protected array $telegramCommands = [];
    protected array $middlewareHandlers = [];

    public function boot(Router $router): void
    {
        // WebHook path in
        $routeKey = ServiceProvider::CONFIG_FILE . '.route';
        $router->post(config($routeKey))->middleware(HandlerMiddleware::class)->name($routeKey);
    }

    public function register(): void
    {
        $this->app->singleton(self::SERVICE_MIDDLEWARE_HANDLERS, fn() => $this->middlewareHandlers);

        $this->app->singleton(
            self::SERVICE_SPECIFIC_UPDATE_HANDLERS,
            fn() => [
                'message' => $this->messageHandlers,
                'edited_message' => $this->editMessageHandlers,
                'channel_post' => $this->channelPostHandlers,
                'edited_channel_post' => $this->editedChannelPostHandlers,
                'inline_query' => $this->inlineQueryHandlers,
                'chosen_inline_result' => $this->chosenInlineResultHandlers,
                'callback_query' => $this->callbackQueryHandlers,
                'shipping_query' => $this->shippingQueryHandlers,
                'pre_checkout_query' => $this->preCheckoutQueryHandlers,
                'poll' => $this->pollHandlers,
                'poll_answer' => $this->pollAnswerHandlers,
            ]
        );

        $this->app->singleton(self::SERVICE_DEFAULT_HANDLERS, fn() => $this->defaultHandlers);

        $this->app->singleton(
            self::SERVICE_COMMAND_HANDLER_COMMANDS,
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
