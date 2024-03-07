## Getting Started

### Installation
Install the `nix-logger/nix-logger-laravel` package:

```bash
composer require nix-logger/nix-logger-laravel
```

### Update .env
```bash
# .env
NIX_LOGGER_API_KEY=
APP_ENV=
NIX_LOGGER_LEVEL=critical,error,warning
```

### Add Log channel:
In `config/logging.php`, add the `nix-logger` logging channel by adding the following under the channels key:
```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['single', 'nix-logger'],
    'ignore_exceptions' => false,
],

'nix-logger' => [
  'driver' => 'custom',
  'via' => \NixLogger\Logger\NixMonologLogger::class,
  'api' => env('ROLLBAR_TOKEN'),
],
```
