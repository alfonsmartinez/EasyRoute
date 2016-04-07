<?php

namespace EasyRoute;

use EasyRoute\Exception\BadRouteException;

class Router
{
    /**
     * @var array
     */
    private $group_options = [];

    /**
     * Collection of routes
     *
     * @var RouteCollection
     */
    private $collection;

    /**
     * Router constructor.
     * @param string $base_uri
     */
    public function __construct($base_uri = "")
    {
        if (!is_string($base_uri)) {
            throw new BadRouteException("Base URI must be a string value");
        }
        $this->collection = new RouteCollection($base_uri);
    }

    /**
     * @param array $options
     * @param \Closure $callback
     */
    public function group(array $options, \Closure $callback)
    {
        $this->_setGroupOptions($options);

        if (is_callable($callback)) {
            $callback($this);
            $this->_resetGroupOptions();
        }
    }

    /**
     * @param string $httpMethod Valid Http method
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return $this
     */
    public function map($httpMethod, $route, $controller)
    {
        $this->collection->addRoute($httpMethod, $route, $this->_getGroupOptions(), $controller);
        return $this;
    }

    /**
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return Router
     */
    public function any($route, $controller)
    {
        return $this->map(Route::ANY, $route, $controller);
    }

    /**
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return Router
     */
    public function get($route, $controller)
    {
        return $this->map(Route::GET, $route, $controller);
    }

    /**
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return Router
     */
    public function head($route, $controller)
    {
        return $this->map(Route::HEAD, $route, $controller);
    }

    /**
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return Router
     */
    public function post($route, $controller)
    {
        return $this->map(Route::POST, $route, $controller);
    }

    /**
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return Router
     */
    public function put($route, $controller)
    {
        return $this->map(Route::PUT, $route, $controller);
    }

    /**
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return Router
     */
    public function patch($route, $controller)
    {
        return $this->map(Route::PATCH, $route, $controller);
    }

    /**
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return Router
     */
    public function delete($route, $controller)
    {
        return $this->map(Route::DELETE, $route, $controller);
    }

    /**
     * @param string $route defined url
     * @param array|callable $controller handler for route
     * @return Router
     */
    public function options($route, $controller)
    {
        return $this->map(Route::OPTIONS, $route, $controller);
    }

    /**
     * @param string $name
     * @param array|callable $handler
     */
    public function filter($name, $handler)
    {
        $this->collection->addfilter($name, $handler);
    }


    /**
     * @param array $options
     * @return $this
     */
    public function where(array $options)
    {
        if (!is_array($options)) {
            throw new BadRouteException("Options must be array");
        }

        $this->collection->addParameters($options);

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function name($name)
    {
        $this->collection->addName($name);
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->collection->getArrayMap();
    }

    /**
     * @param $options
     */
    private function _setGroupOptions($options)
    {
        if (!empty($this->group_options)) {
            $this->group_options[] = array_merge_recursive(end($this->group_options), $options);
        } else {
            $this->group_options[] = $options;
        }
    }

    /**
     *
     */
    private function _resetGroupOptions()
    {
        array_pop($this->group_options);
    }

    /**
     * @return array
     */
    private function _getGroupOptions()
    {
        $options = end($this->group_options);
        return (is_array($options)) ? $options : [];
    }

}