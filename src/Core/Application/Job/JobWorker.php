<?php

namespace Inquisition\Core\Application\Job;

use Throwable;

/**
 * Background worker process
 */
readonly class JobWorker
{

    /**
     * @param JobQueueInterface $queue
     */
    public function __construct(
        private JobQueueInterface $queue
    )
    {
    }

    /**
     * @param string $queueName
     * @return void
     */
    public function work(string $queueName = 'default'): void
    {
        echo "Starting job worker for queue: {$queueName}\n";

        while (true) {
            $job = $this->queue->dequeue($queueName);

            if (!$job) {
                sleep(1);
                continue;
            }

            $this->processJob($job);
        }
    }

    /**
     * @param JobInterface $job
     * @param int $attempt
     * @return void
     */
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
     * @param JobInterface $job
     * @param int $attempt
     * @return void
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

