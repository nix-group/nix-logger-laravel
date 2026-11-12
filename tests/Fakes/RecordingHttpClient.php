<?php

namespace NixLogger\Laravel\Tests\Fakes;

use NixLogger\HttpClient\HttpClientInterface;
use NixLogger\HttpClient\Request;
use NixLogger\HttpClient\Response;

/**
 * Keeps the payloads instead of sending them to the NixLogger API.
 */
class RecordingHttpClient implements HttpClientInterface
{
    /**
     * @var array<int, array>
     */
    public $payloads = [];

    public function sendRequest(Request $request): Response
    {
        $this->payloads[] = json_decode($request->getPayload(), true);

        return new Response(200, [], '');
    }

    /**
     * The payload whose message contains $needle.
     */
    public function find(string $needle): ?array
    {
        foreach ($this->payloads as $payload) {
            if (str_contains($payload['data']['message'] ?? '', $needle)) {
                return $payload;
            }
        }

        return null;
    }
}
