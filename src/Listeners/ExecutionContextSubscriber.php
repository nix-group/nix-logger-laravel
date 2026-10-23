<?php

namespace NixLogger\Laravel\Listeners;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\Looping;
use NixLogger\Context\ExecutionContext;
use Throwable;

/**
 * Keeps the ExecutionContext in sync with what the console is running, so issues
 * reported from commands, queue workers and the scheduler carry that information.
 *
 * Entries are only removed on the "finished/processed" events: a failure is
 * reported after its "failed" event, and must still see the context that failed.
 */
class ExecutionContextSubscriber
{
    public const COMMAND = 'command';

    public const JOB = 'job';

    public const SCHEDULED_TASK = 'scheduledTask';

    /**
     * @var ExecutionContext
     */
    protected $context;

    public function __construct(ExecutionContext $context)
    {
        $this->context = $context;
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(CommandStarting::class, [$this, 'onCommandStarting']);
        $events->listen(CommandFinished::class, [$this, 'onCommandFinished']);
        $events->listen(JobProcessing::class, [$this, 'onJobProcessing']);
        $events->listen(JobProcessed::class, [$this, 'onJobProcessed']);
        $events->listen(Looping::class, [$this, 'onLooping']);
        $events->listen(ScheduledTaskStarting::class, [$this, 'onScheduledTaskStarting']);
        $events->listen(ScheduledTaskFinished::class, [$this, 'onScheduledTaskFinished']);
    }

    public function onCommandStarting(CommandStarting $event): void
    {
        $this->dropFailedCommands();
        $this->context->push(self::COMMAND, [
            'name' => $event->command,
            'input' => $event->input instanceof \Stringable ? (string) $event->input : null,
            'pid' => getmypid() ?: null,
        ]);
    }

    /**
     * Symfony fires this (as ConsoleTerminateEvent) before rethrowing the command's
     * exception, so the console kernel reports it afterwards: a failed command stays
     * in the context, flagged with its exit code, until the next command starts or finishes.
     */
    public function onCommandFinished(CommandFinished $event): void
    {
        $this->dropFailedCommands();

        $current = $this->context->current(self::COMMAND);
        if ($event->exitCode === 0 || ($current['name'] ?? null) !== $event->command) {
            $this->context->pop(self::COMMAND, $event->command);

            return;
        }

        $this->context->pop(self::COMMAND)->push(self::COMMAND, $current + ['exitCode' => $event->exitCode]);
    }

    protected function dropFailedCommands(): void
    {
        while (isset($this->context->current(self::COMMAND)['exitCode'])) {
            $this->context->pop(self::COMMAND);
        }
    }

    public function onJobProcessing(JobProcessing $event): void
    {
        $this->context->push(self::JOB, $this->describeJob($event->job, $event->connectionName));
    }

    public function onJobProcessed(JobProcessed $event): void
    {
        $this->context->pop(self::JOB, $event->job->resolveName());
    }

    /**
     * The worker is about to pick the next job: whatever job context is left
     * belongs to a job that failed and has already been reported.
     */
    public function onLooping(Looping $event): void
    {
        $this->context->forget(self::JOB);
    }

    public function onScheduledTaskStarting(ScheduledTaskStarting $event): void
    {
        $task = $event->task;

        // A failed task never finishes, the next one replaces it.
        $this->context->forget(self::SCHEDULED_TASK)->push(self::SCHEDULED_TASK, [
            'name' => $task->getSummaryForDisplay(),
            'command' => $task->command,
            'expression' => $task->expression,
            'timezone' => $this->timezoneName($task->timezone),
            'withoutOverlapping' => (bool) $task->withoutOverlapping,
            'onOneServer' => (bool) $task->onOneServer,
            'runInBackground' => (bool) $task->runInBackground,
        ]);
    }

    public function onScheduledTaskFinished(ScheduledTaskFinished $event): void
    {
        $this->context->forget(self::SCHEDULED_TASK);
    }

    protected function describeJob(Job $job, ?string $connectionName): array
    {
        try {
            $payload = $job->payload();
        } catch (Throwable $e) {
            $payload = [];
        }

        return array_filter([
            'name' => $job->resolveName(),
            'id' => $job->getJobId(),
            'uuid' => method_exists($job, 'uuid') ? $job->uuid() : null,
            'connection' => $connectionName,
            'queue' => $job->getQueue(),
            'attempts' => $job->attempts(),
            'maxTries' => $job->maxTries(),
            'timeout' => $job->timeout(),
            'batchId' => $this->extractBatchId($payload),
            'pushedAt' => $payload['pushedAt'] ?? null,
        ], function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Read the batch id of a Batchable job straight from the serialized command,
     * without unserializing it (it may be large, encrypted or reference missing classes).
     */
    protected function extractBatchId(array $payload): ?string
    {
        $command = $payload['data']['command'] ?? null;
        if (is_string($command) && preg_match('/s:7:"batchId";s:\d+:"([^"]+)"/', $command, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function timezoneName($timezone): ?string
    {
        if ($timezone instanceof \DateTimeZone) {
            return $timezone->getName();
        }

        return is_string($timezone) ? $timezone : null;
    }
}
