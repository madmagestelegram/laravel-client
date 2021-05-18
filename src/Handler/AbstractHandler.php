<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel\Handler;

use Illuminate\Http\JsonResponse;
use MadmagesTelegram\Laravel\Client;
use MadmagesTelegram\Laravel\WebhookClient;
use MadmagesTelegram\Types\Type\Update;

abstract class AbstractHandler
{

    protected Update $update;
    protected WebhookClient $whClient;
    protected Client $client;

    public function boot(Update $update, WebhookClient $webhookClient, Client $client): void
    {
        $this->update = $update;
        $this->whClient = $webhookClient;
        $this->client = $client;
    }

    abstract public function execute(): ?JsonResponse;

    abstract public function isHandled(): bool;
}
