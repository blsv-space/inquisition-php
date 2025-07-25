<?php

namespace Inquisition\Core\Application\Job;

use Throwable;

interface JobQueueInterface
{
    /**
     * Add a job to the queue
     */
    public function enqueue(JobInterface $job): void;

    /**
     * Get the next job from the queue
     */
    public function dequeue(): ?JobInterface;

    /**
     * Mark a job as completed
     */
    public function complete(JobInterface $job): void;

    /**
     * Mark a job as failed and schedule retry if applicable
     */
    public function fail(JobInterface $job, Throwable $exception, int $attempt): void;

    /**
     * Get queue statistics
     */
    public function getStats(string $queue = 'default'): array;

    /**
     * Clear all jobs from a queue
     */
    public function clear(string $queue = 'default'): void;

    /**
     * @return string
     */
    public function getQueueName(): string;
}
