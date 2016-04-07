<?php

namespace EasyRoute;

use EasyRoute\Exception\BadRouteException;
use EasyRoute\Exception\HttpMethodNotAllowedException;
use EasyRoute\Exception\HttpRouteNotFoundException;

class Dispatcher
{

    /**
     * @var array
     */
    private $route_map = [];

    /**
     * @var RequestInterface|null
     */
    private $request;


    /**
     * Dispatcher constructor.
     * @param array $data
     * @param RequestInterface|null $request
     */
    public function __construct(array $data, RequestInterface $request = null)
    {
        $this->route_map = $data;
        $this->request = $request;
    }


    /**
     * @param RequestInterface|null $request
     * @return bool|mixed|null
     * @throws HttpRouteNotFoundException
     * @throws \Exception
     */
    public function dispatch(RequestInterface $request = null)
    {
        $this->_setRequest($request);

        $requestUrl = $this->request->getBasePath();

        // Strip query string (?a=b) from Request Url
        if (($strpos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $strpos);
        }


        return $this->dispatchRequest($this->request->getMethod(), $requestUrl,
            $this->request->getHost());
    }

    /**
     * @param $httpMethod
     * @param $requestUrl
     * @param bool $domain
     * @return bool|mixed|null
     * @throws HttpMethodNotAllowedException
     * @throws HttpRouteNotFoundException
     */
    public function dispatchRequest($httpMethod, $requestUrl, $domain = false)
    {
        $this->_checkMethod($httpMethod);
        $this->_checkBaseURI($requestUrl);

        if (!isset($this->route_map['routes'][null]) || !is_array($this->route_map['routes'][null])) {
            $this->route_map['routes'][null] = [];
        }
        if (!isset($this->route_map['routes'][$httpMethod]) ||
            !is_array($this->route_map['routes'][$httpMethod])
        ) {
            $this->route_map['routes'][$httpMethod] = [];
        }
        $routes = array_merge($this->route_map['routes'][null], $this->route_map['routes'][$httpMethod]);
        $flag = false;

        foreach ($routes as $route) {

            $route_pattern = $route['regex'];
            if (preg_match($route_pattern, $requestUrl, $arguments)) {
                // Check the domain
                $domain_args = $this->_checkDomain($route, $domain);

                $arguments["request_uri"] = $requestUrl;
                $parameters = array_merge($domain_args, $arguments);

                // before filters //
                if (!empty($routes['befores'])) {
                    foreach ($routes['befores'] as $before) {
                        if ($response = $this->_callFunction($this->route_map['filters'][$before],
                                $parameters) !== null
                        ) {
                            return $response;
                        }
                    }
                }

                $response = $this->_callFunction($route['handler'], $parameters);

                // after filters //
                if (!empty($routes['afters'])) {
                    foreach ($routes['afters'] as $after) {
                        $response = $this->_callFunction($this->route_map['filters'][$after], $parameters, $response);
                    }
                }

                return $response;

            }
        }

        if (!$flag) {
            throw new HttpRouteNotFoundException("Not route found");
        }

        return true;

    }


    /**
     * @param $name
     * @param $options
     * @param RequestInterface $request
     * @return mixed
     * @throws HttpRouteNotFoundException
     */
    public function getUrl($name, $options, RequestInterface $request = null)
    {

        $this->_setRequest($request);

        $domain = $request->getHost();
        $uri = $request->getBasePath();

        if (empty($this->route_map['reverse'][$domain])) {
            throw new BadRouteException("Esta ruta no existe");
        }

        $this->_checkBaseURI($uri);

        $uri = str_replace($this->route_map['base_uri'], "", $uri);
        $prefix = $this->_findPrefix($uri);

        $route = $this->_getRoute($name, $domain, $prefix);

        $url = $route['uri'];
        preg_match_all("@{([^}]+)}@", $url, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $n => $match) {
                $url = str_replace($match, $options[$n], $url);
            }
        }

        return $url;

    }

    /**
     * @param $name
     * @param $options
     * @param $uri
     * @param string $domain
     * @return mixed
     * @throws HttpRouteNotFoundException
     */
    public function getUrlRequest($name, $options, $uri, $domain = "")
    {

        if (empty($this->route_map['reverse'][$domain])) {
            throw new BadRouteException("Esta ruta no existe");
        }

        $this->_checkBaseURI($uri);

        $uri = str_replace($this->route_map['base_uri'], "", $uri);
        $prefix = $this->_findPrefix($uri);

        $route = $this->_getRoute($name, $domain, $prefix);

        $url = $route['uri'];
        preg_match_all("@{([^}]+)}@", $url, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $n => $match) {
                $url = str_replace($match, $options[$n], $url);
            }
        }

        return $url;

    }

    /**
     * @param RequestInterface|null $request
     * @throws \Exception
     */
    private function _setRequest(RequestInterface $request = null)
    {
        if (!$this->request && !$request) {
            throw new \Exception('Request required');
        }
        $this->request = ($request) ? $request : $this->request;
    }

    /**
     * @param $name
     * @param $domain
     * @param $prefix
     * @return mixed
     */
    private function _getRoute($name, $domain, $prefix)
    {
        $route = $this->route_map['reverse'][$domain][$name];

        if (count($route) > 1) {
            foreach ($route as $item) {
                if ($item['prefix'] == $prefix) {
                    $route = $item;
                    break;
                }
            }
        } else {
            $route = $route[0];
        }

        return $route;
    }


    /**
     * @param $controller
     * @param $parameters
     * @param null $_response
     * @return bool|mixed|null
     */
    private function _callFunction($controller, $parameters, $_response = null)
    {
        if (!is_array($controller) && is_callable($controller)) {
            $parameters = $this->_arrangeFuncArgs($controller, $parameters);
        } else {
            if (method_exists($class = $controller[0], $method = $controller[1])) {
                $c = new $class();
                $parameters = $this->_arrangeMethodArgs($c, $method, $parameters);
            }
        }

        // Run controller function or method
        $response = false;
        if (is_callable($controller)) {
            $response = call_user_func_array($controller, $parameters);
        } else {
            if (is_array($controller)) {
                $response = call_user_func_array($controller[0]->$controller[1], $parameters);
            }
        }

        return ($_response) ? $_response : $response;
    }

    /**
     * @param $uri
     * @return string
     * @throws HttpRouteNotFoundException
     */
    private function _findPrefix($uri)
    {
        foreach ($this->route_map['prefixs'] as $prefix) {
            $prefix = substr($prefix, 1, -1);
            if (preg_match("@$prefix@", $uri, $matches)) {
                return $prefix;
            }
        }

        throw new HttpRouteNotFoundException("Esta ruta no existe");
    }

    /**
     * @param $uri
     * @throws HttpRouteNotFoundException
     */
    private function _checkBaseURI($uri)
    {
        if (substr($uri, 0, strlen($this->route_map['base_uri'])) != $this->route_map['base_uri']) {
            throw new HttpRouteNotFoundException(404);
        }
    }


    /**
     * @param $function
     * @param $arguments
     * @return array
     */
    private function _arrangeFuncArgs($function, $arguments)
    {
        $ref = new \ReflectionFunction($function);
        return array_map(
            function (\ReflectionParameter $param) use ($arguments) {
                if (isset($arguments[$param->getName()])) {
                    return $arguments[$param->getName()];
                }
                if ($param->isOptional()) {
                    return $param->getDefaultValue();
                }
                return null;
            },
            $ref->getParameters()
        );
    }

    /**
     * @param $class
     * @param $method
     * @param $arguments
     * @return array
     */
    private function _arrangeMethodArgs($class, $method, $arguments)
    {
        $ref = new \ReflectionMethod($class, $method);
        return array_map(
            function (\ReflectionParameter $param) use ($arguments) {
                if (isset($arguments[$param->getName()])) {
                    return $arguments[$param->getName()];
                }
                if ($param->isOptional()) {
                    return $param->getDefaultValue();
                }
                return null;
            },
            $ref->getParameters()
        );
    }

    /**
     * @param $httpMethod
     * @throws HttpMethodNotAllowedException
     */
    private function _checkMethod($httpMethod)
    {

        $httpMethod = strtoupper($httpMethod);
        if (!defined("\\EasyRoute\\Route::$httpMethod")) {
            throw new HttpMethodNotAllowedException("Not method $httpMethod allowed");
        }
    }

    /**
     * @param $route
     * @param $domain
     * @return array
     * @throws HttpRouteNotFoundException
     */
    private function _checkDomain($route, $domain)
    {

        if (!empty($route['domain']) && $route['domain'] != $domain) {
            throw new HttpRouteNotFoundException(404);
        } else {
            return [
                'domain' => $domain
            ];
        }

    }
}