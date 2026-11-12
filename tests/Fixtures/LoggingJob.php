<?php

namespace NixLogger\Laravel\Tests\Fixtures;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Catches its own failure and logs it, like most batch/IF jobs do.
 */
class LoggingJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue;

    public function __construct(public string $message = 'LoggingJob caught') {}

    public function handle(): void
    {
        try {
            throw new RuntimeException('inner failure');
        } catch (RuntimeException $e) {
            Log::error($this->message, ['exception' => $e]);
        }
    }
}
