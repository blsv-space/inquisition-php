<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Job;

use Throwable;

abstract class AbstractSyncJob implements JobInterface
{
    public function __construct(
        public array $payload = [] {
            get {
                return $this->payload;
            }
        },
    )
    {
    }

    #[\Override]
    abstract public function handle();

    /**
     * Execute the job directly
     *
     * @throws Throwable
     */
    #[\Override]
    public function execute(): mixed
    {
        try {
            $result = $this->handle();
            $this->onSuccess($result);

            return $result;
        } catch (Throwable $exception) {
            $this->onFailure($exception);
            throw $exception;
        }
    }

    #[\Override]
    public function getName(): string
    {
        return static::class;
    }

    #[\Override]
    public function isAsync(): bool
    {
        return false;
    }

    #[\Override]
    public function getMaxRetries(): int
    {
        return 0;
    }

    #[\Override]
    public function getRetryDelay(): int
    {
        return 0;
    }

    #[\Override]
    public function getPriority(): int
    {
        return 0;
    }

    #[\Override]
    public function getQueueName(): string
    {
        return 'sync';
    }

    #[\Override]
    public function shouldRetry(Throwable $exception, int $attempt): bool
    {
        return false;
    }

    #[\Override]
    public function onFailure(Throwable $exception): void {}

    #[\Override]
    public function onSuccess(mixed $result): void {}
}
