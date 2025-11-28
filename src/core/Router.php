<?php
class Router {
    private $routes = [];
    
    public function addRoute($route, $controller, $method = 'GET') {
        $this->routes[] = [
            'route' => $route,
            'controller' => $controller,
            'method' => $method
        ];
    }
    
    public function dispatch($requestUri, $requestMethod) {
        foreach ($this->routes as $route) {
            if ($this->matches($route['route'], $requestUri) && $route['method'] === $requestMethod) {
                return $route['controller'];
            }
        }
        return null;
    }
    
    private function matches($route, $requestUri) {
        $pattern = str_replace('/', '\/', $route);
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $pattern);
        return preg_match("/^{$pattern}$/", $requestUri);
    }
}