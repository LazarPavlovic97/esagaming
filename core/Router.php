<?php
namespace App\Core;

class Router {
    private $routes = [];

    public function add(Route $route) {
        $this->routes[] = $route;
    }

    public function find(string $httpMethod, string $url) {
        foreach ($this->routes as $route) {
            if ($route->matches($httpMethod, $url)) {
                return $route;
            }
        }

        return null;
    }
}
