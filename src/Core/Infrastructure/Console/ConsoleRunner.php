<?php

namespace Inquisition\Core\Infrastructure\Console;

use Exception;
use Inquisition\Core\Application\Console\Command\CommandInterface;
use Inquisition\Core\Application\Console\Provider\CommandProviderInterface;
use Inquisition\Core\Application\Console\Provider\MigrationCommandProvider;
use InvalidArgumentException;
use Throwable;

final class ConsoleRunner
{
    /**
     * @var class-string[]
     */
    private array $commands = [];
    /**
     * @var CommandProviderInterface[]
     */
    private array $providers = [];

    /**
     * @param CommandProviderInterface $provider
     * @return void
     * @throws Exception
     */
    public function addProvider(CommandProviderInterface $provider): void
    {
        $class = $provider::class;
        if (isset($this->providers[$class])) {
            throw new Exception("Provider '$class' already registered");
        }
        $this->providers[$class] = $provider;
        $this->commands = array_merge($this->commands, $provider->getCommands());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function bootstrap(): void
    {
        $this->addProvider(new MigrationCommandProvider());
    }

    /**
     * Run a console command
     *
     * @param string $commandName The name of the command to run
     * @param array $arguments Command arguments and options
     * @return void
     * @throws InvalidArgumentException If command is not found
     * @throws Exception|Throwable If job class doesn't exist or can't be instantiated
     */
    public function run(string $commandName, array $arguments = []): void
    {
        if (!isset($this->commands[$commandName])) {
            throw new InvalidArgumentException(
                "Command '$commandName' not found. Available commands:\r\n" .
                implode(",\r\n", array_keys($this->commands))
            );
        }

        $commandClass = $this->commands[$commandName];

        if (!class_exists($commandClass)
            && is_subclass_of($commandClass, CommandInterface::class)
        ) {
            throw new Exception("Command class '$commandClass' not found for command '$commandName'");
        }
        /**
         * @var CommandInterface $command
         */
        $command = new $commandClass($arguments);

        try {
            echo "Executing command: $commandName\n";
            $startTime = microtime(true);

            $command->execute();

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            echo "Command '$commandName' completed in {$executionTime}ms\n";

            return;

        } catch (Throwable $exception) {
            echo "Command '$commandName' failed: {$exception->getMessage()}\n";

            if ($this->isDebugMode($arguments)) {
                echo "Stack trace:\n{$exception->getTraceAsString()}\n";
            }

            throw $exception;
        }
    }

    /**
     * Get a list of available commands
     *
     * @return array
     */
    public function listCommands(): array
    {
        return array_keys($this->commands);
    }

    /**
     * Get detailed information about all commands
     *
     * @return array
     */
    public function getCommandsInfo(): array
    {
        $info = [];
        foreach ($this->commands as $commandName => $jobClass) {
            $info[$commandName] = [
                'name' => $commandName,
                'job_class' => $jobClass,
                'exists' => class_exists($jobClass)
            ];
        }

        return $info;
    }

    /**
     * Check if debug mode is enabled
     *
     * @param array $arguments
     * @return bool
     */
    private function isDebugMode(array $arguments): bool
    {
        return isset($arguments['debug']) ||
            isset($arguments['verbose']) ||
            isset($arguments['v']);
    }
}