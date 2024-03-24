<?php

namespace NixLogger\Laravel;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use NixLogger\Client;
use NixLogger\Configuration;
use NixLogger\Laravel\Request\NixLoggerLaravelHttpRequest;

class NixLoggerServiceProvider extends ServiceProvider
{
    /**
     * The package version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig($this->app);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->bind(NixLogger::class, function () {
            $config = $this->getLoggerConfig();
            $nixLoggerRequest = new NixLoggerLaravelHttpRequest();
            if (! $this->app->runningInConsole()) {
                $request = $this->app->make(Request::class);
                $nixLoggerRequest->setLaravelRequest($request);
            } else {
                // todo
            }
            $client = new Client($config, $nixLoggerRequest);

            return $client;
        });
    }

    private function getLoggerConfig()
    {
        $config = $this->getConfig();

        $loggerConfig = new Configuration($config['api_key'] ?? '');
        $loggerConfig->setEnvironment($this->app->environment());
        $loggerConfig->setRootPath(base_path());
        $loggerConfig->setTimeZone(config('app.timezone'));
        $loggerConfig->mergeDeviceData(['runtimeVersions' => $this->getRuntimeVersion()]);
        $loggerConfig->setSdkIdentifier(Version::getSdkIdentifier());
        $loggerConfig->setVersion(Version::getVersion());
        $loggerConfig->setRunningMode($this->app->runningInConsole() ? 'cli' : 'web');
        $loggerConfig->setLoggerReportLevel($config['logger_report_level'] ?? '');

        return $loggerConfig;
    }

    protected function getRuntimeVersion()
    {
        $version = $this->app->version();

        return ['laravel' => $version];
    }

    /**
     * Retrieve the user configuration.
     */
    protected function getConfig(): array
    {
        $config = $this->app['config']->get($this->getName());

        return empty($config) ? [] : $config;
    }

    /**
     * Retrieve Plugin Name
     */
    protected function getName(): string
    {
        return 'nix-logger';
    }

    /**
     * Setup the config.
     *
     *
     * @return void
     */
    protected function setupConfig(Container $app)
    {
        $source = realpath($raw = __DIR__.'/../config/nix-logger.php') ?: $raw;

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => config_path('nix-logger.php')]);
        } elseif ($app instanceof LumenApplication) {
            $app->configure('nix-logger');
        }

        $this->mergeConfigFrom($source, 'nix-logger');
    }
}
