<?php

namespace NixLogger\Laravel\Request;

use Illuminate\Http\Request;
use NixLogger\Request\NixLoggerHttpRequest;
use Exception;

class NixLoggerLaravelHttpRequest extends NixLoggerHttpRequest
{
    public function setLaravelRequest(Request $request)
    {
        $this->setUrl($request->fullUrl());
        $this->setHttpMethod($request->getMethod());
        $this->setParams($request->input());
        $this->setBody($request->all());
        $this->setClientIp($request->getClientIp());
        $this->setUserAgent($request->header('User-Agent'));
        $this->setHeaders($request->headers->all());
        try {
            $this->setSessions($request->session()->all());
        } catch (Exception $e) {
            $this->setSessions([]);
        }
        $this->setCookies($request->cookies->all());

        return $this;
    }
}
