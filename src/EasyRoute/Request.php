<?php

namespace EasyRoute;


class Request implements RequestInterface
{

    public function getMethod()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    public function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }

    public function getBasePath()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }
}