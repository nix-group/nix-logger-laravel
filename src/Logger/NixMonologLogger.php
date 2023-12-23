<?php

namespace NixLogger\Logger;

use Monolog\Logger;
use NixLogger\Handlers\NixLoggerHandler;

class NixMonologLogger
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
