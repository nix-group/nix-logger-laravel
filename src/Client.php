<?php

namespace NixLogger;

use NixLogger\HttpClient\HttpClient;
use NixLogger\HttpClient\Request;
use NixLogger\Serializer\PayloadSerializer;
use NixLogger\Item;

class Client
{
    /**
     * The config instance.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * The config instance.
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Create New NixLogger Client
     * 
     * @param \NixLogger\Configuration $config
     */
    public function __construct(
        Configuration $config,
    ) {
        $this->config = $config;
        $this->httpClient = new HttpClient($this->config);
    }

    // public function report(Report $report, callable $callback = null)
    public function report($message)
    {
        $request = new Request();
        $item = new Item($message);
        $request->setPayload(PayloadSerializer::serialize($item));

        $this->httpClient->sendRequest($request);
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
     *
     * @return bool
     */
    public function enabled(): bool
    {
        return !!($this->getConfig()->getApiKey());
    }
}
