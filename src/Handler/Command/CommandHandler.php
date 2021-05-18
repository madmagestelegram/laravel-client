<?php declare(strict_types=1);

namespace MadmagesTelegram\Laravel\Handler\Command;

use Generator;
use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use MadmagesTelegram\Laravel\Handler\AbstractHandler;
use MadmagesTelegram\Laravel\HandlerServiceProvider;
use MadmagesTelegram\Laravel\ServiceProvider;
use MadmagesTelegram\Types\Type\Message;
use RuntimeException;

class CommandHandler extends AbstractHandler
{

    private ?AbstractCommand $foundCommand = null;
    private bool $isPrivate;
    /** @var AbstractCommand[] */
    private array $commands;
    private bool $isCommandForAnotherBot = false;

    public function __construct(Container $container)
    {
        $this->commands = $container->get(HandlerServiceProvider::SERVICE_COMMAND_HANDLER_COMMANDS);
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

        foreach ($this->getCommands() as [$commandName, $isPrivate]) {
            if (isset($commands[$commandName])) {
                $this->foundCommand = $commands[$commandName];
                $this->isPrivate = $isPrivate;

                return true;
            }
        }

        if ($this->isCommandForAnotherBot) {
            // Dot not pass this update for another handlers
            return true;
        }

        return false;
    }

    private function getCommands(): Generator
    {
        /** @var Message $message */
        $message = $this->update->getMessage();
        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup', 'private'], true)) {
            return;
        }

        $botName = config(ServiceProvider::CONFIG_FILE . '.bot_name');

        foreach ($message->getEntities() as $entity) {
            if ($entity->getType() !== 'bot_command') {
                continue;
            }

            $rawCommand = substr($message->getText(), $entity->getOffset(), $entity->getLength());
            if ($rawCommand[0] !== '/') {
                throw new RuntimeException("Unexpected command start char: Expecting '/' got '{$rawCommand[0]}'");
            }
            $trimmedCommand = ltrim($rawCommand, '/');

            if ($chatType === 'private') {
                yield [$trimmedCommand, true];
            } elseif (strpos($trimmedCommand, '@') !== false) {
                [$commandName, $botNameInCommand] = explode('@', $trimmedCommand);
                $this->isCommandForAnotherBot = $botNameInCommand !== $botName;
                if ($this->isCommandForAnotherBot) {
                    break;
                }

                yield [$commandName, false];
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
