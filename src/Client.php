<?php

namespace NixLogger;

use NixLogger\HttpClient\HttpClient;
use NixLogger\HttpClient\Request;
use NixLogger\Request\NixLoggerHttpRequest;
use NixLogger\Resolvers\IssueResolver;
use NixLogger\Serializer\PayloadSerializer;
use Psr\Log\LogLevel;
use Monolog\LogRecord;

class Client
{
    /**
     * The config instance.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * The request instance.
     *
     * @var NixLoggerHttpRequest
     */
    protected $request;

    /**
     * The config instance.
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Create New NixLogger Client
     */
    public function __construct(
        Configuration $config,
        NixLoggerHttpRequest $request,
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->httpClient = new HttpClient($this->config);
    }

    /**
     * @var LogRecord|Throwable|string|Stringable $message,
     */
    public function report(
        string $level,
        $message,
        array $context = [],
    ) {
        $issueResolver = new IssueResolver($this->config, $this->request);
        $payload = $issueResolver->getPayload($level, $message, $context);
        $request = new Request();
        $request->setPayload(PayloadSerializer::serialize($payload));
        $response = $this->httpClient->sendRequest($request);
        return $response;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->report($level, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function reportUncaught(LogRecord $record): void
    {
        $this->report(LogLevel::ERROR, $record, $record['context'] ?? []);
    }

    /**
     * Returns the client config
     *
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns true if the NixLogger is enabled.
     */
    public function enabled(): bool
    {
        return (bool) ($this->getConfig()->getApiKey());
    }
}
