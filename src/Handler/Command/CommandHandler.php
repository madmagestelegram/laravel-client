<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel\Handler\Command;

use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use MadmagesTelegram\Laravel\Handler\AbstractHandler;
use MadmagesTelegram\Laravel\HandlerServiceProvider;
use MadmagesTelegram\Laravel\ServiceProvider;
use MadmagesTelegram\Types\Type\Message;

class CommandHandler extends AbstractHandler
{

    /** @var AbstractCommand */
    private $foundCommand;
    /** @var bool */
    private bool $isPrivate;
    /** @var AbstractCommand[] */
    private $commands;
    private $shouldReact = true;

    public function __construct(Container $container)
    {
        $this->commands = $container->get(HandlerServiceProvider::SERVICE_COMMANDS);
    }

    /**
     * @return bool
     */
    public function isHandled(): bool
    {
        if (empty($this->commands)) {
            return false;
        }

        $message = $this->update->getMessage();
        if ($message === null || $message->getEntities() === null) {
            return false;
        }

        /** @var AbstractCommand[] $commands */
        $commands = [];
        foreach ($this->commands as $command) {
            $commands[$command->getName()] = $command;
        }

        try {
            foreach ($this->getCommands() as [$commandName, $isPrivate]) {
                if (isset($commands[$commandName])) {
                    $this->foundCommand = $commands[$commandName];
                    $this->isPrivate = $isPrivate;
                    return true;
                }
            }
        } catch (\Throwable $exception) {
            $this->foundCommand = null;
            return true;
        }

        if (!$this->shouldReact) {
            return true;
        }

        return false;
    }

    private function getCommands(): \Generator
    {
        /** @var Message $message */
        $message = $this->update->getMessage();
        $chatType = $message->getChat()->getType();
        $botName = config(ServiceProvider::CONFIG_FILE . '.bot_name');

        foreach ($message->getEntities() as $entity) {
            if ($entity->getType() === 'bot_command') {
                $rawCommand = substr($message->getText(), $entity->getOffset(), $entity->getLength());
                if ($rawCommand[0] !== '/') {
                    throw new \RuntimeException(
                        "Unexpected command start char:  expecting '/' got '{$rawCommand[0]}'"
                    );
                }
                $trimmedCommand = ltrim($rawCommand, '/');

                if ($chatType === 'private') {
                    $this->shouldReact = true;
                    yield [$trimmedCommand, true];
                }

                if (in_array($chatType, ['group', 'supergroup'], true)) {
                    if (strpos($trimmedCommand, '@') !== false) {
                        [$commandName, $botNameInCommand] = explode('@', $trimmedCommand);
                        if ($botNameInCommand !== $botName) {
                            $this->shouldReact = false;
                            break;
                        }

                        $this->shouldReact = true;
                        yield [$commandName, false];
                    } else {
                        $this->shouldReact = false;
                        break;
                    }
                }
            }
        }
    }

    public function execute(): ?JsonResponse
    {
        if ($this->foundCommand === null) {
            return null;
        }

        return $this->foundCommand->execute($this->update->getMessage(), $this->isPrivate);
    }
}
