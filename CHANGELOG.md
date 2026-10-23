## Changelog

### 1.4.0
- Attach the running console command, queued job (incl. batch id) and scheduled task to every issue, sent as `extraData`.
- New config `capture_execution_context` (`NIX_LOGGER_CAPTURE_EXECUTION_CONTEXT`, default `true`).
- A bound `NixLogger\HttpClient\HttpClientInterface` replaces the HTTP client (tests).
- Requires `nix-logger/nix-logger` `^1.4`.
- Feature tests with Orchestra Testbench (Laravel 10, 11, 12).
