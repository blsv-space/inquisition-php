<?php

namespace Inquisition\Core\Application\Console\Command;

abstract readonly class AbstractCommand implements CommandInterface
{
    /**
     * Ask user for input
     */
    protected function ask(string $question): string
    {
        echo $question . ' ';
        return trim(fgets(STDIN));
    }

    /**
     * Ask a user to confirm an action
     */
    protected function confirm(string $question): bool
    {
        $response = $this->ask($question . ' (y/N):');
        return in_array(strtolower($response), ['y', 'yes', '1', 'true']);
    }

    /**
     * Print info message to CLI
     */
    protected function info(string $message): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Print a red error message to CLI
     */
    protected function error(string $message): void
    {
        echo "\033[31m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Print a green success message to CLI
     */
    protected function success(string $message): void
    {
        echo "\033[32m" . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Execute the command
     */
    abstract public function execute(): void;

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return 'No description provided.';
    }

    /**
     * Get command help text
     */
    public function getHelp(): string
    {
        return 'No help provided.';
    }

    /**
     * Get command alias
     */
    abstract public function getAlias(): string;

    /**
     * Get command arguments
     */
    public function getArguments(): array
    {
        return [];
    }
}
