<?php

namespace Inquisition\Core\Infrastructure\Console;

use Exception;
use Inquisition\Core\Application\Console\Provider\CommandProviderInterface;
use Inquisition\Core\Application\Console\Provider\CacheCommandProvider;
use Inquisition\Core\Application\Console\Provider\MigrationCommandProvider;
use Inquisition\Core\Application\Console\Provider\QueueCommandProvider;
use InvalidArgumentException;
use Throwable;

final class ConsoleRunner
{
    private array $commands = [];
    private array $providers = [];

    /**
     * @param CommandProviderInterface $provider
     * @return void
     * @throws Exception
     */
    public function addProvider(CommandProviderInterface $provider): void
    {
        if (isset($this->providers[$provider::class])) {
            throw new Exception("Provider '{$provider::class}' already registered");
        }
        $this->providers[$provider::class] = $provider;
        $this->commands = array_merge($this->commands, $provider->getCommands());
    }

    public function bootstrap(): void
    {
        $this->addProvider(new MigrationCommandProvider());
        $this->addProvider(new QueueCommandProvider());
        $this->addProvider(new CacheCommandProvider());
    }

    /**
     * Run a console command
     *
     * @param string $commandName The name of the command to run
     * @param array $arguments Command arguments and options
     * @return mixed The result of the command execution
     * @throws InvalidArgumentException If command is not found
     * @throws Exception|Throwable If job class doesn't exist or can't be instantiated
     */
    public function run(string $commandName, array $arguments = []): mixed
    {
        if (!isset($this->commands[$commandName])) {
            throw new InvalidArgumentException(
                "Command '{$commandName}' not found. Available commands:\r\n" .
                implode(",\r\n", array_keys($this->commands))
            );
        }

        $jobClass = $this->commands[$commandName];

        if (!class_exists($jobClass)) {
            throw new Exception("Job class '{$jobClass}' not found for command '{$commandName}'");
        }

        try {
            $payload = array_merge($arguments, [
                'command' => $commandName,
                'timestamp' => time()
            ]);

            $job = new $jobClass($payload);

            echo "Executing command: {$commandName}\n";
            $startTime = microtime(true);

            $result = $this->dispatcher->dispatch($job, $arguments);

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            echo "Command '{$commandName}' completed in {$executionTime}ms\n";

            return $result;

        } catch (Throwable $exception) {
            echo "Command '{$commandName}' failed: {$exception->getMessage()}\n";

            if ($this->isDebugMode($arguments)) {
                echo "Stack trace:\n{$exception->getTraceAsString()}\n";
            }

            throw $exception;
        }
    }

    /**
     * Get list of available commands
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