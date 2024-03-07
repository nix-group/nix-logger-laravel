<?php

return [
    'api_key' => env('NIX_LOGGER_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Logger report level
    |--------------------------------------------------------------------------
    |
    | This sets the level at which a logged message will report to Nix logger
    | Default: 'critical,error,warning'.
    |
    | Must be one of the Psr\Log\LogLevel levels from the Psr specification.
    |
    */
    'logger_report_level' => env('NIX_LOGGER_LEVEL', 'critical,error,warning'),
];
