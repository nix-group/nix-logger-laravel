## Getting Started

### Installation
Install the `nix-logger/nix-logger-laravel` package:

```bash
composer require nix-logger/nix-logger-laravel
```
### Add the service provider to the `providers` array in `config/app.php` before your `AppServiceProvider::class`
```php
NixLogger\NixLoggerServiceProvider::class,
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
  'via' => \NixLogger\Logger\NixLogger::class,
  'api' => env('ROLLBAR_TOKEN'),
],
```
