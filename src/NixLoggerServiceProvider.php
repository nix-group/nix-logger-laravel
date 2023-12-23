<?php

namespace NixLogger;

use Illuminate\Support\ServiceProvider;

class NixLoggerServiceProvider extends ServiceProvider
{

  /**
   * Register the service provider.
   */
  public function register(): void
  {
    $this->app->bind($this->getName(), function () {
      $config = $this->getUserConfig();
      $client = new Client(new Configuration($config['api_key'] ?? ''));

      return $client;
    });
  }

  /**
   * Retrieve the user configuration.
   *
   * @return array
   */
  protected function getUserConfig(): array
  {
    $config = $this->app['config']->get($this->getName());
    return empty($config) ? [] : $config;
  }

  /**
   * Retrieve Plugin Name
   *
   * @return string
   */
  protected function getName(): string
  {
    return 'nix-logger';
  }
}
