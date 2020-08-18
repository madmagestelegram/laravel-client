<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel\Handler\Command;

use Illuminate\Http\JsonResponse;
use MadmagesTelegram\Types\Type\Message;

abstract class AbstractCommand
{

    abstract public function getName(): string;

    abstract public function execute(Message $message, bool $isPrivate): ?JsonResponse;
}
