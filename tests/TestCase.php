<?php

namespace NixLogger\Laravel\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\WithConsoleEvents;
use Illuminate\Support\Facades\Schema;
use NixLogger\HttpClient\HttpClientInterface;
use NixLogger\Laravel\Logger\NixLogger;
use NixLogger\Laravel\NixLoggerServiceProvider;
use NixLogger\Laravel\Tests\Fakes\RecordingHttpClient;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    // Laravel does not fire CommandStarting/CommandFinished in unit tests without it
    use WithConsoleEvents;

    protected RecordingHttpClient $http;

    protected function getPackageProviders($app)
    {
        return [NixLoggerServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('nix-logger.api_key', 'test-key');
        $app['config']->set('logging.default', 'nix-logger');
        $app['config']->set('logging.channels.nix-logger', [
            'driver' => 'custom',
            'via' => NixLogger::class,
        ]);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('queue.default', 'database');
        $app['config']->set('queue.connections.database.connection', 'testing');
        $app['config']->set('queue.batching.database', 'testing');
        $app['config']->set('queue.failed', ['driver' => 'database-uuids', 'database' => 'testing', 'table' => 'failed_jobs']);

        $this->http = new RecordingHttpClient;
        $app->instance(HttpClientInterface::class, $this->http);
    }

    protected function defineDatabaseMigrations()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }
}
