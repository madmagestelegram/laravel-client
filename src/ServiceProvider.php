<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use MadmagesTelegram\Types\Type\Update;
use MadmagesTelegram\Types\TypedClient;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public const CONFIG_FILE = 'madmages_telegram';

    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/config.php' => config_path(self::CONFIG_FILE . '.php'),
            ]
        );

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    SetWebhook::class,
                ]
            );
        }
    }

    public function register(): void
    {
        $this->app->singleton(
            Client::class,
            static function () {
                $guzzleConfigs = config(self::CONFIG_FILE . '.guzzle_configs');
                if (empty($guzzleConfigs)) {
                    $client = null;
                } else {
                    $client = new \GuzzleHttp\Client($guzzleConfigs);
                }

                return new Client(config(self::CONFIG_FILE . '.token'), $client);
            }
        );

        $this->app->singleton(
            Update::class,
            static function (Container $app) {
                return TypedClient::deserialize($app->get(Request::class)->getContent(), Update::class);
            }
        );
    }
}
