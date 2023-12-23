<?php

namespace NixLogger\Request;

use Illuminate\Http\Request;

class NixLoggerHttpRequest
{
    private $url;

    private $httpMethod;

    private $params;
    
    private $body;

    private $clientIp;

    private $userAgent;

    private $headers;

    private $session;

    private $cookies;

    public function setLaravelRequest(Request $request)
    {
        $this->url = $request->fullUrl();
        $this->httpMethod = $request->getMethod();
        $this->params = $request->input();
        $this->clientIp = $request->getClientIp();
        $this->body = $request->all();
        $this->userAgent = $request->header('User-Agent');
        $this->headers = $request->headers->all();
        $this->cookies = $request->cookies->all();

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getClientIp()
    {
        return $this->clientIp;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

}
