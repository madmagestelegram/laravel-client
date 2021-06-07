<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel\Command;

use Illuminate\Console\Command;
use MadmagesTelegram\Laravel\Client;
use MadmagesTelegram\Laravel\ServiceProvider;
use MadmagesTelegram\Types\TelegramException;
use function config;

class SetWebhook extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'madmagestelegram:registerWebhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register telegram webhook';

    /**
     * Execute the console command.
     *
     * @param Client $client
     * @return mixed
     * @throws TelegramException
     */
    public function handle(Client $client): int
    {
        $webhookPath = (string)config(ServiceProvider::CONFIG_FILE . '.route');
        $webhookHost = config(ServiceProvider::CONFIG_FILE . '.webhook_host');
        if (empty($webhookHost)) {
            $webhookHost = config('app.url');
        }
        $finalUrl = $webhookHost . $webhookPath;
        if (!$this->confirm("Set webhook url: {$finalUrl}")) {
            return 0;
        }

        if (($result = $client->setWebhook($finalUrl)) === true) {
            echo "Telegram hook was set on url: {$finalUrl}";
        } else {
            echo "Webhook was not set!";
        }

        return (int)!$result;
    }
}
