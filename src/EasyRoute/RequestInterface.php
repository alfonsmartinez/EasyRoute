<?php

namespace EasyRoute;

interface RequestInterface
{

    /**
     * @return string Http Method
     */
    public function getMethod();

    /**
     * @return string Host
     */
    public function getHost();

    /**
     * @return string Base path
     */
    public function getBasePath();

}