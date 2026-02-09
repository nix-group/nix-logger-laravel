<?php

namespace NixLogger\Laravel;

final class Version
{
    public const SDK_IDENTIFIER = 'nix-logger.php.laravel';

    public const SDK_VERSION = '1.3.7';

    public static function getSdkIdentifier()
    {
        return self::SDK_IDENTIFIER;
    }

    public static function getVersion()
    {
        return self::SDK_VERSION;
    }
}
