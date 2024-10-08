<?php

namespace NixLogger\Laravel\Handlers;

use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;
use NixLogger\Laravel\NixLogger;

class NixLoggerStreamHandler extends StreamHandler
{
    /**
     * \Monolog\LogRecord | array: $record
     */
    protected function write($record): void
    {
        app(NixLogger::class)->reportUncaught($record);
        parent::write($record);
    }
}
