<?php

namespace NixLogger\HttpClient;

/**
 * @internal
 */
final class Request
{
    /**
     * @var string
     */
    private $payload;

    public function hasPayload(): bool
    {
        return $this->payload !== null;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }
}
