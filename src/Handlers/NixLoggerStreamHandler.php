<?php

namespace NixLogger\Laravel\Handlers;

use Monolog\LogRecord;
use NixLogger\Laravel\NixLogger;
use Monolog\Handler\StreamHandler;

class NixLoggerStreamHandler extends StreamHandler
{
    protected function write(LogRecord $record): void
    {
        app(NixLogger::class)->reportUncaught($record);
        parent::write($record);
    }
}
