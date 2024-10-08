## Getting Started

### Installation
Install the `nix-logger/nix-logger-laravel` package:

```bash
composer require nix-logger/nix-logger-laravel
```
### Add the service provider 
Add provider `NixLogger\Laravel\NixLoggerServiceProvider::class`
- For Laravel < 11, update `config/app.php
```php
// config/app.php
'providers' => [
    Illuminate\Auth\AuthServiceProvider::class,
    NixLogger\Laravel\NixLoggerServiceProvider::class,
]
```
- For Laravel >= 11, update `bootstrap/providers.php`
```php
// bootstrap/providers.php
return [
    Illuminate\Auth\AuthServiceProvider::class,
    NixLogger\Laravel\NixLoggerServiceProvider::class,
];
```

### Add the Facade
- For Laravel < 11, update `config/app.php`
```php
// config/app.php
'aliases' => [
    'NixLogger' => NixLogger\Laravel\Facades\NixLogger::class,
]
```
- For Laravel >= 11, update `bootstrap/app.php`
```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'NixLogger' => NixLogger\Laravel\Facades\NixLogger::class,
    ]);
})
```


### Configuration
Configure your Nix-Logger by edit the `.env` file
```bash
# .env
NIX_LOGGER_API_KEY=
APP_ENV=
NIX_LOGGER_LEVEL=critical,error,warning
```
If you’d like to configure Nix-Logger further, create and edit a `config/nix-logger.php` file



### Setting Log report
In `config/logging.php`, add the `nix-logger` logging channel by adding the following under the `channels` key:
```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['single', 'nix-logger'],
    'ignore_exceptions' => false,
],

'nix-logger' => [
  'driver' => 'custom',
  'via' => \NixLogger\Laravel\Logger\NixLogger::class,
],
```

## For `stderr`
```php
'channels' => [
    'stderr' => [
        'driver' => 'monolog',
        'level' => env('LOG_LEVEL', 'debug'),
        'handler' => \NixLogger\Laravel\Handlers\NixLoggerStreamHandler::class,
        'formatter' => env('LOG_STDERR_FORMATTER'),
        'with' => [
            'stream' => 'php://stderr',
        ],
        'processors' => [PsrLogMessageProcessor::class],
    ],
]
```


## Usage
```php
\NixLogger::debug('Test');
```
