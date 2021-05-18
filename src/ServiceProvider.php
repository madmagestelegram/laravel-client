<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use MadmagesTelegram\Laravel\Command\SetWebhook;
use MadmagesTelegram\Types\Serializer;
use MadmagesTelegram\Types\Type\Update;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public const CONFIG_FILE = 'madmages_telegram';

    public function boot(): void
    {
        $this->publishes([__DIR__ . '/config.php' => config_path(self::CONFIG_FILE . '.php')]);

        if ($this->app->runningInConsole()) {
            $this->commands([SetWebhook::class]);
        }
    }

    public function register(): void
    {
        $this->app->singleton(
            Client::class,
            static function () {
                $guzzleConfigs = config(self::CONFIG_FILE . '.guzzle_configs');

                $guzzleClient = null;
                if (!empty($guzzleConfigs)) {
                    $guzzleClient = new \GuzzleHttp\Client($guzzleConfigs);
                }

                return new Client(config(self::CONFIG_FILE . '.token'), $guzzleClient);
            }
        );

        $this->app->singleton(
            Update::class,
            static fn(Container $app) => Serializer::deserialize($app->get(Request::class)->getContent(), Update::class)
        );
    }
}
