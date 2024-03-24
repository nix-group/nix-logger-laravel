<?php

namespace NixLogger\Laravel\Logger;

use Monolog\Logger;
use NixLogger\Laravel\Handlers\NixLoggerHandler;

class NixLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('custom');
        $logger->pushHandler(new NixLoggerHandler());

        return $logger;
    }
}
