<?php

namespace Cdc;

class Dispatcher {

    /**
     *
     * @var Router
     */
    public $router;

    /**
     *
     * @var Route
     */
    public $matchedRoute;

    public function __construct(\Cdc\Router $router, $matched_route) {
        $this->router = $router;
        $this->matchedRoute = $matched_route;
    }

    /**
     *
     * @return \Cdc\Router
     */
    public function getRouter() {
        return $this->router;
    }

    public function setRouter($router) {
        $this->router = $router;
    }

    /**
     *
     * @return \Cdc\Route
     */
    public function getMatchedRoute() {
        return $this->matchedRoute;
    }

    public function setMatchedRoute($matchedRoute) {
        $this->matchedRoute = $matchedRoute;
    }

    public function dispatch() {
        $target = $this->matchedRoute->getTarget();

        if (array_key_exists('class', $target)) {
            $class = $target['class'];

            if (array_key_exists('action', $target)) {
                $action = $target['action'] . 'Action';
            } else {
                $action = 'indexAction';
            }

            $obj = new $class;

            if (method_exists($obj, $action)) {
                return array($obj, $action, $this->matchedRoute->getParameters());
            } else {
                throw new \Cdc\Exception\Dispatch\ActionNotFound;
            }
        }

        throw new \Cdc\Exception\Dispatch;
    }

}
