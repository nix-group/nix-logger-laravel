<?php

namespace NixLogger;

class Item
{
    /**
     * @var float|null The date and time of when this event was generated
     */
    private $timestamp;

    /**
     * @var Severity|null The severity of this event
     */
    private $level;

    /**
     * @var string|null The error message
     */
    private $message;

    /**
     * @var string|null The environment where this event generated (e.g. production)
     */
    private $environment;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Gets the timestamp of when this event was generated.
     */
    public function getTimestamp(): ?float
    {
        return $this->timestamp;
    }

    /**
     * Sets the timestamp of when the Event was created.
     */
    public function setTimestamp(?float $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getLevel()
    {
        return $this->level;
    }
    public function setLevel($level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }
    public function setMessage($message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }
    public function setEnvironment($environment): self
    {
        $this->environment = $environment;

        return $this;
    }
}
