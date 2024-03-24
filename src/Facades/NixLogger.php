<?php

namespace NixLogger\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use NixLogger\Laravel\NixLogger as BaseNixLogger;

class NixLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BaseNixLogger::class;
    }
}
