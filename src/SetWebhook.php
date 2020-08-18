<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel;

use Illuminate\Console\Command;

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
     */
    public function handle(Client $client)
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

        $result = $client->setWebhook($webhookPath);
        echo "Telegram hook was set on url: {$webhookPath}";
        return (int)!$result;
    }
}
