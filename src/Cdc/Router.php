<?php

/**
 * Cdc Toolkit
 *
 * Copyright 2012 Eduardo Marinho
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * 	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Eduardo Marinho
 * @package Cdc
 *
 *
 *
 *
 * Based on third-party code found on
 * https://github.com/dannyvankooten/PHP-Router
 * and subject to the following license:
 *
 *
 *
 * Copyright (c) 2012 Danny van Kooten
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
/**
 * Routing class to match request URL's against given routes and map them to a controller action.
 */

namespace Cdc;

class Router {

    /**
     * Array that holds all Route objects
     * @var array
     */
    private $routes = array();
    private $fallbackRoutes = array();

    /**
     * Array to store named routes in, used for reverse routing.
     * @var array
     */
    private $namedRoutes = array();

    /**
     * The base REQUEST_URI. Gets prepended to all route url's.
     * @var string
     */
    private $basePath = '';
    public $args;

    /**
     * Set the base url - gets prepended to all route url's.
     * @param string $base_url
     */
    public function setBasePath($basePath) {
        $this->basePath = (string) $basePath;
    }

    public function getNamedRoutes() {
        return $this->namedRoutes;
    }

    /**
     * Route factory method
     *
     * Maps the given URL to the given target.
     * @param string $routeUrl string
     * @param mixed $target The target of this route. Can be anything. You'll have to provide your own method to turn this into a filename, controller / action pair, etc..
     * @param array $args Array of optional arguments.
     */
    public function map($routeUrl, $target = '', array $args = array()) {
        $route = new \Cdc\Route;

        $route->setUrl($this->basePath . $routeUrl);

        $route->setTarget($target);

        if (isset($args['methods'])) {
            $methods = explode(',', $args['methods']);
            $route->setMethods($methods);
            unset($args['methods']);
        }

        if (isset($args['filters'])) {
            $route->setFilters($args['filters']);
            unset($args['filters']);
        }

        if (isset($args['name'])) {
            $route->setName($args['name']);
            $this->namedRoutes[$route->getName()] = $route;
        } else {
            $route->setName($routeUrl);
            $this->namedRoutes[$route->getName()] = $route;
        }
        unset($args['name']);

        if (isset($args['fallback'])) {
            $this->fallbackRoutes[] = $route;
        } else {
            $this->routes[] = $route;
        }
        unset($args['fallback']);

        $route->args = $args;
    }

    /**
     * Matches the current request against mapped routes
     */
    public function matchCurrentRequest() {
        $requestMethod = (isset($_POST['_method']) && ($_method = strtoupper($_POST['_method'])) && in_array($_method, array('PUT', 'DELETE'))) ? $_method : $_SERVER['REQUEST_METHOD'];
        $requestUrl = $_SERVER['REQUEST_URI'];

// strip GET variables from URL
        if (($pos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $pos);
        }

        return $this->match($requestUrl, $requestMethod);
    }

    public function matchRequestFromControllerAction($controller, $action) {
        $routeSources = array($this->routes, $this->fallbackRoutes);
        foreach ($routeSources as $routes) {
            foreach ($routes as $route) {
                $target = $route->getTarget();

                if ($target['class'] == $controller && isset($target['action']) && $target['action'] . 'Action' == $action) {
                    return $route;
                }
            }
        }
    }

    /**
     * Match given request url and request method and see if a route has been defined for it
     * If so, return route's target
     * If called multiple times
     */
    public function match($requestUrl, $requestMethod = 'GET') {
        $routeSources = array($this->routes, $this->fallbackRoutes);

        foreach ($routeSources as $routes) {
            foreach ($routes as $route) {

// compare server request method with route's allowed http methods
                if (!in_array($requestMethod, $route->getMethods()))
                    continue;

// check if request url matches route regex. if not, return false.
                if (!preg_match("@^" . $route->getRegex() . "*$@i", $requestUrl, $matches))
                    continue;

                $params = array();

                if (preg_match_all("/:([\w-]+)/", $route->getUrl(), $argument_keys)) {

// grab array with matches
                    $argument_keys = $argument_keys[1];

// loop trough parameter names, store matching value in $params array
                    foreach ($argument_keys as $key => $name) {
                        if (isset($matches[$key + 1]))
                            $params[$name] = $matches[$key + 1];
                    }
                }

                $route->setParameters($params);

                return $route;
            }
        }



        return false;
    }

    /**
     * Reverse route a named route
     *
     * @param string $route_name The name of the route to reverse route.
     * @param array $params Optional array of parameters to use in URL
     * @param array $query_params HTTP GET params
     * @return string The url to the route
     */
    public function generate($routeName, array $params = array(), array $query_params = array()) {
// Check if route exists
        if (!isset($this->namedRoutes[$routeName]))
            throw new \Exception("No route with the name $routeName has been found.");

        $route = $this->namedRoutes[$routeName];
        $url = $route->getUrl();

// replace route url with given parameters
        if ($params && preg_match_all("/:([\w-]+)/", $url, $param_keys)) {

// grab array with matches
            $param_keys = $param_keys[1];

// loop trough parameter names, store matching value in $params array
            foreach ($param_keys as $i => $key) {
                if (isset($params[$key]))
                    $url = preg_replace("/:([\w-]+)/", $params[$key], $url, 1);
            }
        }

        if ($query_params) {
            $q = '?' . http_build_query($query_params);
        } else {
            $q = null;
        }

        return $url . $q;
    }

}
