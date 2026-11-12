<?php

namespace NixLogger\Laravel\Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use NixLogger\Laravel\Tests\TestCase;
use RuntimeException;

class ScheduleContextTest extends TestCase
{
    public function test_failing_scheduled_task_carries_the_task(): void
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->call(function () {
            throw new RuntimeException('scheduled sync exploded');
        })->name('stock-sync')->everyMinute()->timezone('Asia/Tokyo')->withoutOverlapping();
        $schedule->call(function () {
            Log::error('second task logs');
        })->name('jan-issue')->everyMinute();

        $this->artisan('schedule:run')->run();

        $failed = $this->http->find('scheduled sync exploded')['extraData'];
        $this->assertSame('schedule:run', $failed['command']['name']);
        $this->assertSame('stock-sync', $failed['scheduledTask']['name']);
        $this->assertSame('* * * * *', $failed['scheduledTask']['expression']);
        $this->assertSame('Asia/Tokyo', $failed['scheduledTask']['timezone']);
        $this->assertTrue($failed['scheduledTask']['withoutOverlapping']);

        // the failed task does not stick to the next one
        $next = $this->http->find('second task logs')['extraData'];
        $this->assertSame('jan-issue', $next['scheduledTask']['name']);
        $this->assertArrayNotHasKey('scheduledTaskStack', $next);
    }
}
