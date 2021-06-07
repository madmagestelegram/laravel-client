<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel;

use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use MadmagesTelegram\Laravel\Handler\AbstractHandler;
use MadmagesTelegram\Types\Type\Update;
use RuntimeException;

class HandlerMiddleware
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(): JsonResponse
    {
        $specificUpdateHandlers = $this->container->get(HandlerServiceProvider::SERVICE_SPECIFIC_UPDATE_HANDLERS);
        $defaultHandlers = $this->container->get(HandlerServiceProvider::SERVICE_DEFAULT_HANDLERS);
        $middlewareHandlers = $this->container->get(HandlerServiceProvider::SERVICE_MIDDLEWARE_HANDLERS);

        /** @var Update $update */
        $update = $this->container->get(Update::class);

        // #1 - middleware
        $foundHandler = $this->findHandler($middlewareHandlers);

        // #2 - specific handler
        if ($foundHandler === null) {
            foreach ($update->_getData() as $key => $value) {
                if ($value === null) {
                    continue;
                }

                if (
                    !empty($specificUpdateHandlers[$key])
                    && $foundHandler = $this->findHandler($specificUpdateHandlers[$key])
                ) {
                    break;
                }
            }
        }

        // #2 - default handler
        if ($foundHandler === null) {
            $foundHandler = $this->findHandler($defaultHandlers);
        }


        if ($foundHandler !== null) {
            $response = $this->container->call([$foundHandler, 'execute']);
            if ($response) {
                return $response;
            }

            return response()->json();
        }

        throw new RuntimeException('Telegram request not handled');
    }

    private function findHandler(array $handlerClasses): ?AbstractHandler
    {
        foreach ($handlerClasses as $handlerClass) {
            /** @var AbstractHandler $handler */
            $handler = $this->container->make($handlerClass);
            $this->container->call([$handler, 'boot']);
            if ($handler->isHandled()) {
                return $handler;
            }
        }

        return null;
    }
}
