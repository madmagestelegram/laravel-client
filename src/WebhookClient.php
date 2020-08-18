<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel;

use Illuminate\Http\JsonResponse;
use MadmagesTelegram\Types\TypedClient;

class WebhookClient extends \MadmagesTelegram\Types\Client
{

    /**
     * Method call handler
     *
     * @param string $method
     * @param array  $parameters
     * @return mixed
     * @throws \JsonException
     */
    public function _rawApiCall(string $method, array $parameters): JsonResponse
    {
        $parameters['method'] = $method;

        $parameters = array_filter($parameters);
        if (empty($parameters)) {
            return response()->json();
        }

        array_walk_recursive(
            $parameters,
            static function (&$item) {
                if (!is_object($item)) {
                    return;
                }

                $item = json_decode(TypedClient::serialize($item), true, 512, JSON_THROW_ON_ERROR);
                $item = array_filter($item);
            }
        );

        return response()->json($parameters);
    }
}
