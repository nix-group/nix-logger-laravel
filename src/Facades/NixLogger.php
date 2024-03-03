<?php

namespace NixLogger\Facades;

use Illuminate\Support\Facades\Facade;
use NixLogger\NixLogger as BaseNixLogger;

class NixLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BaseNixLogger::class;
    }
}
