<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Job;

use Throwable;

/**
 * Background worker process
 */
readonly class JobWorker
{
    public function __construct(
        private JobQueueInterface $queue,
    ) {}

    public function work(): void
    {
        echo "Starting job worker for queue: {$this->queue->getQueueName()}\n";

        while (true) {
            $job = $this->queue->dequeue();

            if (!$job) {
                sleep(1);
                continue;
            }

            $this->processJob($job);
        }
    }

    private function processJob(JobInterface $job, int $attempt = 1): void
    {
        try {
            $this->execute($job, $attempt);
            $this->queue->complete($job);
            echo "Job completed: {$job->getName()}\n";
        } catch (Throwable $exception) {
            echo "Job failed: {$job->getName()} - {$exception->getMessage()}\n";
            $this->queue->fail($job, $exception, $attempt);
        }
    }

    /**
     * @throws Exception\JobException
     */
    private function execute(JobInterface $job, int $attempt = 1): void
    {
        try {
            $result = $job->handle();
            $job->onSuccess($result);
            return;
        } catch (Throwable $exception) {
            if (!$job->shouldRetry($exception, $attempt)) {
                $job->onFailure($exception);
            }
            throw $exception;
        }
    }
}
