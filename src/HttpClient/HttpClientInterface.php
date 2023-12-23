<?php

namespace NixLogger\HttpClient;

interface HttpClientInterface
{
    public function sendRequest(Request $request): Response;
}
