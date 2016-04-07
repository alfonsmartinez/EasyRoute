<?php

namespace EasyRoute;

class RouteCollection
{

    /**
     * @var string
     */
    private $base_uri;

    /**
     * @var  Route
     */
    private $lastRoute;

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var array
     */
    private $domains = [];

    /**
     * @var array
     */
    private $prefixs = [];

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var array
     */
    private $reverse = [];

    /**
     * RouteCollection constructor.
     * @param string $base_uri
     */
    public function __construct($base_uri = "")
    {
        $this->base_uri = $base_uri;
    }

    /**
     * @param $httpMethod
     * @param $route
     * @param $options
     * @param $handler
     */
    public function addRoute($httpMethod, $route, $options, $handler)
    {
        if (!is_array($httpMethod)) {
            $httpMethod = array($httpMethod);
        }

        $this->_setPrefixs($this->_getPrefix($options));
        $this->_setDomains($options);

        $options = array_merge($options, ['base_uri' => $this->base_uri]);
        $this->lastRoute = new Route($route, $options, $handler);

        foreach ($httpMethod as $method) {
            $this->routes[$method][] = $this->lastRoute->getData();
        }
    }

    /**
     * @param $parameters
     */
    public function addParameters($parameters)
    {
        foreach ($parameters as $name => $regex) {
            $this->lastRoute->setParameter($name, $regex);
        }
    }

    /**
     * @param $name
     */
    public function addName($name)
    {
        $this->reverse[$this->lastRoute->getDomain()][$name][] = $this->lastRoute->getData();
    }

    /**
     * @param $name
     * @param $handler
     */
    public function addFilter($name, $handler)
    {
        $this->filters[$name] = $handler;
    }

    /**
     * @return array
     */
    public function getArrayMap()
    {

        return [
            'base_uri' => $this->base_uri,
            'prefixs'  => $this->prefixs,
            'routes'   => $this->_orderRoutes(),
            'reverse'  => $this->reverse,
            'filters'  => $this->filters
        ];
    }

    /**
     * @param $options
     */
    private function _setDomains($options)
    {
        if (!empty($options['domain']) && !in_array($options['domain'], $this->domains)) {
            $this->domains[] = $options['domain'];
        }
    }

    /**
     * @param $prefix
     */
    private function _setPrefixs($prefix)
    {
        if (!in_array($prefix, $this->prefixs)) {
            $this->prefixs[] = $prefix;
            usort($this->prefixs, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
        }
    }

    /**
     * @param $options
     * @return string
     */
    private function _getPrefix($options)
    {
        $prefix = "";
        if (!empty($options['prefix'])) {
            $prefix = $options['prefix'];
            if (is_array($options['prefix'])) {
                $prefix = implode('/', $options['prefix']);
            }
            $prefix = '/' . $prefix . '/';
        }
        return $prefix;
    }

    /**
     * @return array
     */
    private function _orderRoutes()
    {
        foreach($this->routes as $method => $routes){
            usort($this->routes[$method], function($a, $b) {
                return $a['domain'] - $b['domain'];
            });
        }

        return $this->routes;
    }
}