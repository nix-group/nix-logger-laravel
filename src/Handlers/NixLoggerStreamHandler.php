<?php

namespace NixLogger\Laravel\Handlers;

use Monolog\Handler\StreamHandler;
use Monolog\LogRecord;
use NixLogger\Laravel\NixLogger;

class NixLoggerStreamHandler extends StreamHandler
{
    protected function write(LogRecord $record): void
    {
        app(NixLogger::class)->reportUncaught($record);
        parent::write($record);
    }
}
