<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel;

use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use MadmagesTelegram\Laravel\Handler\AbstractHandler;
use MadmagesTelegram\Types\Type\Update;
use RuntimeException;

class HandlerMiddleware
{
    private ?AbstractHandler $foundHandler;
    private Container $container;


    public function __construct(Update $update, Container $container)
    {
        $this->container = $container;

        $specificUpdateHandlers = $container->get(HandlerServiceProvider::SERVICE_SPECIFIC_UPDATE_HANDLERS);
        $defaultHandlers = $container->get(HandlerServiceProvider::SERVICE_DEFAULT_HANDLERS);
        $middlewareHandlers = $container->get(HandlerServiceProvider::SERVICE_MIDDLEWARE_HANDLERS);

        // #1 - middleware
        $this->foundHandler = $this->findHandler($middlewareHandlers);

        // #2 - specific handler
        if ($this->foundHandler === null) {
            foreach ($update->_getData() as $key => $value) {
                if ($value === null) {
                    continue;
                }

                if (
                    !empty($specificUpdateHandlers[$key])
                    && $this->foundHandler = $this->findHandler($specificUpdateHandlers[$key])
                ) {
                    break;
                }
            }
        }

        // #2 - default handler
        if ($this->foundHandler === null) {
            $this->foundHandler = $this->findHandler($defaultHandlers);
        }
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

    public function handle(): JsonResponse
    {
        if ($this->foundHandler !== null) {
            $response = $this->container->call([$this->foundHandler, 'execute']);
            if ($response) {
                return $response;
            }

            return response()->json();
        }

        throw new RuntimeException('Request not handled');
    }
}
