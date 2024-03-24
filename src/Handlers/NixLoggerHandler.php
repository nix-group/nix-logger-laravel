<?php

namespace NixLogger\Laravel\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use NixLogger\Laravel\NixLogger;

class NixLoggerHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        app(NixLogger::class)->reportUncaught($record);
    }
}
