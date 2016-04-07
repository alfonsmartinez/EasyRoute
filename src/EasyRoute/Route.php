<?php

namespace EasyRoute;

//https://github.com/mrjgreen/phroute/tree/v3/examples
//https://laravel.com/docs/5.2/routing#route-groups

use EasyRoute\Exception\HttpRouteNotFoundException;

class Route
{
    /**
     * Constants for common HTTP methods
     */
    const ANY = 'ANY';
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';

    /**
     * @var string
     */
    private $route;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array|callable
     */
    private $handler;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * Route constructor.
     * @param $route
     * @param $options
     * @param $handler
     * @param string $base_uri
     */
    public function __construct($route, $options, $handler, $base_uri = "")
    {
        $this->route = $route;
        $this->options = $options;
        $this->uri = $this->_setUri();
        $this->handler = $handler;
    }

    /**
     * @return mixed|string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getUriRegex()
    {
        return $this->_convertToRegex($this->getUri());
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        if(!empty($this->options['domain'])){
            return $this->options['domain'];
        }

        return '';
    }

    /**
     * @param $domain
     * @return array
     * @throws HttpRouteNotFoundException
     */
    public function checkDomain($domain)
    {
        if ($domain) {
            return $this->_checkDomain($domain);
        }
        return [];
    }

    /**
     * @param $name
     * @param $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @return array|bool
     */
    public function getBefores()
    {
        if (empty($this->options['before'])) {
            return false;
        } else {
            return (!is_array($this->options["before"])) ? array($this->options["before"]) : $this->options["before"];
        }
    }

    /**
     * @return array|bool
     */
    public function getAfters()
    {
        if (empty($this->options['after'])) {
            return false;
        } else {
            return (!is_array($this->options["after"])) ? array($this->options["after"]) : $this->options["after"];
        }
    }

    /**
     * @return array|callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->_getPrefix();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'after'   => $this->getAfters(),
            'before'  => $this->getBefores(),
            'uri'     => $this->getUri(),
            'regex'   => $this->getUriRegex(),
            'handler' => $this->getHandler(),
            'domain'  => $this->getDomain(),
            'prefix'  => $this->getPrefix()
        ];
    }

    /**
     * @param $requestdomain
     * @return array
     * @throws HttpRouteNotFoundException
     */
    private function _checkDomain($requestdomain)
    {
        if (!empty($this->options['domain'])) {
            if (preg_match($this->_convertToRegex($this->options['domain']), $requestdomain, $arguments)) {
                return $arguments;
            }
            throw new HttpRouteNotFoundException(404);
        }
        return [];
    }

    /**
     * @param $content
     * @return mixed
     */
    private function _safeRegex($content)
    {
        $f = array('(', ')');
        $r = array('\(', '\)');
        return str_replace($f, $r, $content);
    }

    /**
     * @return string
     */
    private function _getPrefix()
    {
        $prefix = "";
        if (!empty($this->options['prefix'])) {
            $prefix = $this->options['prefix'];
            if (is_array($this->options['prefix'])) {
                $prefix = implode('/', $this->options['prefix']);
            }
        }
        return $prefix;
    }

    /**
     * @param $route
     * @return string
     */
    private function _convertToRegex($route)
    {
        return '@^' . preg_replace_callback("@{([^}]+)}@", function ($match) {
            return $this->_regexParameter($match[0]);
        }, $route) . '[/]?$@';
    }

    /**
     * @param $name
     * @return string
     */
    private function _regexParameter($name)
    {
        $name = str_replace(array('{', '}'), array('', ''), $name);
        $pattern = isset($this->parameters[$name]) ? $this->parameters[$name] : "[^/]+";
        return '(?<' . $name . '>' . $pattern . ')';
    }

    /**
     * @return mixed
     */
    private function _setUri()
    {
        $uri = '';
        if (!empty($this->options['base_uri'])) {
            $uri .= $this->options['base_uri'];
        }
        if (!empty($this->_getPrefix())) {
            $uri .= '/'. $this->_getPrefix();
        }

        if(substr($this->route, -1) == '/'){
            $this->route = substr($this->route, 0, -1);
        }
        return $this->_safeRegex($uri . $this->route);
    }

}