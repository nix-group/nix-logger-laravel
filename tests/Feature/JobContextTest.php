<?php

namespace NixLogger\Laravel\Tests\Feature;

use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use NixLogger\Context\ExecutionContext;
use NixLogger\Laravel\Tests\Fixtures\FailingJob;
use NixLogger\Laravel\Tests\Fixtures\LoggingJob;
use NixLogger\Laravel\Tests\TestCase;

class JobContextTest extends TestCase
{
    public function test_failing_job_on_a_worker_carries_job_command_and_trace(): void
    {
        FailingJob::dispatch()->onQueue('stock');

        $this->artisan('queue:work', ['--once' => true, '--queue' => 'stock'])->run();

        $payload = $this->http->find('FailingJob exploded');
        $this->assertNotNull($payload);
        $this->assertNotEmpty($payload['data']['trace']);
        $this->assertSame('queue:work', $payload['extraData']['command']['name']);

        $job = $payload['extraData']['job'];
        $this->assertSame(FailingJob::class, $job['name']);
        $this->assertSame('database', $job['connection']);
        $this->assertSame('stock', $job['queue']);
        $this->assertSame(1, $job['attempts']);
        $this->assertSame(3, $job['maxTries']);
        $this->assertNotEmpty($job['id']);
        $this->assertNotEmpty($job['uuid']);
    }

    public function test_error_logged_inside_a_job_carries_the_job(): void
    {
        LoggingJob::dispatch('IF_TOOL_RUN_THREW: pdb_sku_jan_issue');

        $this->artisan('queue:work', ['--once' => true])->run();

        $payload = $this->http->find('IF_TOOL_RUN_THREW: pdb_sku_jan_issue');
        $this->assertStringStartsWith('IF_TOOL_RUN_THREW: pdb_sku_jan_issue | Exception: inner failure', $payload['data']['message']);
        $this->assertNotEmpty($payload['data']['trace']);
        $this->assertSame(LoggingJob::class, $payload['extraData']['job']['name']);
    }

    public function test_daemon_worker_does_not_leak_a_failed_job_into_the_next_one(): void
    {
        FailingJob::dispatch();
        LoggingJob::dispatch('second job logs');

        $this->artisan('queue:work', ['--stop-when-empty' => true])->run();

        $this->assertSame(FailingJob::class, $this->http->find('FailingJob exploded')['extraData']['job']['name']);
        $second = $this->http->find('second job logs')['extraData'];
        $this->assertSame(LoggingJob::class, $second['job']['name']);
        $this->assertArrayNotHasKey('jobStack', $second);
    }

    public function test_looping_clears_the_job_left_by_a_failure(): void
    {
        FailingJob::dispatch();
        $this->artisan('queue:work', ['--once' => true])->run();

        $this->assertNotNull($this->app[ExecutionContext::class]->current('job'));

        event(new Looping('database', 'default'));
        Log::error('worker idle');

        $this->assertArrayNotHasKey('job', $this->http->find('worker idle')['extraData'] ?? []);
    }

    public function test_job_context_is_removed_once_processed(): void
    {
        Queue::connection('sync')->push(new LoggingJob('sync job logs'));
        Log::error('after the sync job');

        $this->assertSame(LoggingJob::class, $this->http->find('sync job logs')['extraData']['job']['name']);
        $this->assertSame('sync', $this->http->find('sync job logs')['extraData']['job']['connection']);
        $this->assertNull($this->http->find('after the sync job')['extraData']);
    }

    public function test_batched_job_carries_the_batch_id(): void
    {
        $batch = Bus::batch([new LoggingJob('batched job logs')])->name('stock-sync')->dispatch();

        $this->artisan('queue:work', ['--once' => true])->run();

        $this->assertSame($batch->id, $this->http->find('batched job logs')['extraData']['job']['batchId']);
    }
}
