<?php

namespace NixLogger\Laravel\Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use NixLogger\Context\ExecutionContext;
use NixLogger\Laravel\Tests\TestCase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

class CommandContextTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app->booted(function () {
            Artisan::command('batch:throws {--date=}', function () {
                throw new RuntimeException('batch:throws exploded');
            });
            Artisan::command('batch:logs', function () {
                Log::error('batch:logs caught', ['exception' => new RuntimeException('inner')]);
            });
            Artisan::command('batch:outer', function () {
                Artisan::call('batch:logs');
                Log::warning('batch:outer after inner');
            });
            Artisan::command('batch:outer-catches', function () {
                try {
                    Artisan::call('batch:throws');
                } catch (RuntimeException $e) {
                    Log::warning('batch:outer-catches recovered');
                }
            });
        });
    }

    /**
     * Run like `php artisan ...`. The real console kernel reports what escapes the
     * command, the Testbench one rethrows it: report it the same way.
     */
    private function runArtisan(array $argv): void
    {
        try {
            $this->app[Kernel::class]->handle(
                new ArgvInput(array_merge(['artisan'], $argv)),
                new BufferedOutput
            );
        } catch (Throwable $e) {
            $this->app[ExceptionHandler::class]->report($e);
        }
    }

    public function test_uncaught_command_exception_carries_command_and_trace(): void
    {
        $this->runArtisan(['batch:throws', '--date=2026-10-23']);

        $payload = $this->http->find('batch:throws exploded');
        $this->assertNotNull($payload);
        $this->assertSame('cli', $payload['runningMode']);
        $this->assertSame('LogRecord@UnCaughtException', $payload['data']['type']);
        $this->assertNotEmpty($payload['data']['trace']);
        $this->assertSame('batch:throws', $payload['extraData']['command']['name']);
        $this->assertStringContainsString('--date=2026-10-23', $payload['extraData']['command']['input']);
        $this->assertSame(getmypid(), $payload['extraData']['command']['pid']);
        $this->assertSame(1, $payload['extraData']['command']['exitCode']);
    }

    public function test_failed_nested_command_does_not_stick_once_the_outer_one_finishes(): void
    {
        $this->runArtisan(['batch:outer-catches']);
        Log::error('after the commands');

        $recovered = $this->http->find('batch:outer-catches recovered');
        // the caught inner failure is still the latest thing the process ran
        $this->assertSame('batch:throws', $recovered['extraData']['command']['name']);
        $this->assertSame(['batch:outer-catches', 'batch:throws'], $recovered['extraData']['commandStack']);

        $this->assertTrue($this->app[ExecutionContext::class]->isEmpty());
        $this->assertNull($this->http->find('after the commands')['extraData']);
    }

    public function test_logged_error_inside_command_carries_command(): void
    {
        $this->runArtisan(['batch:logs']);

        $payload = $this->http->find('batch:logs caught');
        $this->assertSame('batch:logs', $payload['extraData']['command']['name']);
        $this->assertArrayNotHasKey('commandStack', $payload['extraData']);
        $this->assertNotEmpty($payload['data']['trace']);
    }

    public function test_nested_command_reports_the_command_stack(): void
    {
        $this->runArtisan(['batch:outer']);

        $inner = $this->http->find('batch:logs caught');
        $this->assertSame('batch:logs', $inner['extraData']['command']['name']);
        $this->assertSame(['batch:outer', 'batch:logs'], $inner['extraData']['commandStack']);

        $outer = $this->http->find('batch:outer after inner');
        $this->assertSame('batch:outer', $outer['extraData']['command']['name']);
        $this->assertArrayNotHasKey('commandStack', $outer['extraData']);
    }

    public function test_context_is_cleared_once_the_command_finished(): void
    {
        $this->runArtisan(['batch:logs']);
        Log::error('after the command');

        $this->assertTrue($this->app[ExecutionContext::class]->isEmpty());
        $this->assertNull($this->http->find('after the command')['extraData']);
    }

    #[DefineEnvironment('disableCapture')]
    public function test_capture_can_be_disabled(): void
    {
        $this->runArtisan(['batch:logs']);

        $this->assertNull($this->http->find('batch:logs caught')['extraData']);
    }

    protected function disableCapture($app): void
    {
        $app['config']->set('nix-logger.capture_execution_context', false);
    }
}
