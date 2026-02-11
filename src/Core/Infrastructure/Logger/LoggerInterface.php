<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Logger;

/**
 * Logger Interface
 * Defines the contract for logging functionality across the application
 */
interface LoggerInterface
{
    /**
     * System is unusable
     */
    public function emergency(string $message, array $context = []): void;

    /**
     * Action must be taken immediately
     */
    public function alert(string $message, array $context = []): void;

    /**
     * Critical conditions
     */
    public function critical(string $message, array $context = []): void;

    /**
     * Runtime errors that do not require immediate action
     */
    public function error(string $message, array $context = []): void;

    /**
     * Exceptional occurrences that are not errors
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Normal but significant events
     */
    public function notice(string $message, array $context = []): void;

    /**
     * Interesting events
     */
    public function info(string $message, array $context = []): void;

    /**
     * Detailed debug information
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Log with arbitrary level
     */
    public function log(LogLevelEnum $level, string $message, array $context = []): void;

    /**
     * Set the minimum log level
     */
    public LogLevelEnum $level {
        get;
        set;
    }

    /**
     * Add context to all subsequent log entries
     */
    public function withContext(array $context): self;

    /**
     * Set the log channel/category
     */
    public function channel(string $channel): self;

    public string $channel {
        get;
    }
}
