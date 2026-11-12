## Getting Started

### Installation
Install the `nix-logger/nix-logger-laravel` package:

```bash
composer require nix-logger/nix-logger-laravel
```
### Add the service provider 
Add provider `NixLogger\Laravel\NixLoggerServiceProvider::class`
#### Check Version
```bash
php artisan --version
# Laravel Framework xx.xx.xx
```
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
NIX_LOGGER_API_KEY=""
NIX_LOGGER_LEVEL=critical,error,warning
LOG_CHANNEL="stack"
LOG_STACK="daily,nix-logger"
# APP_ENV=
```
If you’d like to configure Nix-Logger further, create and edit a `config/nix-logger.php` file



### Setting Log report
In `config/logging.php`, add the `nix-logger` logging channel by adding the following under the `channels` key:
```php
'stack' => [
    'driver' => 'stack',
    'channels' => explode(',', env('LOG_STACK', 'single')),
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


## Console commands, queued jobs and scheduled tasks
HTTP issues carry their request. Issues reported from the console carry what was running instead, in `extraData`:

| Key | Filled while | Fields |
| --- | --- | --- |
| `command` | an artisan command runs (`queue:work`, `schedule:run`, your batches...) | `name`, `input`, `pid`, `exitCode` (failed command) |
| `job` | a queued job is processed (worker or `sync` connection) | `name`, `id`, `uuid`, `connection`, `queue`, `attempts`, `maxTries`, `timeout`, `batchId`, `pushedAt` |
| `scheduledTask` | the scheduler runs a task | `name`, `command`, `expression`, `timezone`, `withoutOverlapping`, `onOneServer`, `runInBackground` |
| `commandStack`, `jobStack` | executions are nested (`Artisan::call()` inside a command...) | names, outer → inner |

```json
{
  "runningMode": "cli",
  "extraData": {
    "command": { "name": "queue:work", "input": "'queue:work' --queue=stock", "pid": 4182 },
    "job": { "name": "App\\Jobs\\SyncStock", "id": "1532", "uuid": "9f0c...", "connection": "database", "queue": "stock", "attempts": 2, "maxTries": 3 }
  }
}
```
Nothing to configure. To turn it off:
```bash
NIX_LOGGER_CAPTURE_EXECUTION_CONTEXT=false
```
A command run with `$this->call()` from another command is not tracked separately (Laravel fires no event for it): its issues carry the outer command. `Artisan::call()` is tracked.

## Usage
### Use NixLogger Debug
```bash
php artisan tinker
```
```php
\NixLogger::error('Test Error');
```

### Log a caught exception
Always pass the exception as `exception` in the context: NixLogger builds the trace from it, and keeps your message in front of the exception message.
```php
try {
    $task->run();
} catch (Throwable $e) {
    // Issue message: "IF_TOOL_RUN_THREW: pdb_sku_jan_issue | Exception: ... in /app/...:107"
    Log::error('IF_TOOL_RUN_THREW: pdb_sku_jan_issue', ['exception' => $e, 'run_id' => $runId]);
}
```
Putting `$e->getTraceAsString()` in the context instead sends no trace, only a string.

### Text with example exception
```php
Route::get('/error', function () {
    throw new Exception('This is a test exception');
    
    return [
        'message' => 'This is a test response',
    ];
});
```
Open browser and navigate to [http://localhost:8000/error](http://localhost:8000/error)

## Testing
```bash
composer install
composer test
```
To test against a local checkout of `nix-logger/nix-logger` (sibling folder `../nix-logger-php`), use a `composer.local.json` with a path repository (ignored by git):
```bash
COMPOSER=composer.local.json composer update
vendor/bin/phpunit
```
