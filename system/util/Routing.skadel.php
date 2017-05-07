<?php

namespace skadel\system\util;


class Routing {

    private static $routes = [];

    public function __construct($routes) {
        self::$routes = $routes;
        $this->buildRoutes();
    }

    public function dispatch($uri) {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        foreach (self::$routes as $key => $route) {
            if (preg_match('/^' . str_replace('/', '\/', $route['route']) . '$/xU', $uri, $args) != false) {
                if (class_exists($route['controller'][0])) {
                    if (!isset($route['method']) || $route['method'] == $requestMethod) {
                        $tmp = [];
                        foreach ($route['arguments'] as $arg => $regex) {
                            $tmp[$arg] = isset($args[$arg]) ? $args[$arg] : null;
                        }

                        Template::assign('activeRoute', $route['name']);

                        return [
                            'controller' => $route['controller'],
                            'arguments' => $tmp
                        ];
                    }
                }
                break;
            }
        }
        return ['controller' => '\skadel\system\controller\error\Error404', 'arguments' => []];
    }

    public function exec($route) {
        try {
            $rc = new \ReflectionClass($route['controller'][0]);
            $func = $route['controller'][1];
        } catch (\Exception $e) {
            $rc = new \ReflectionClass('\skadel\system\controller\error\Error404');
            $func = 'display';
        }

        $cb = $rc->newInstance();

        if ($cb && !method_exists($cb, $func)) {
            $this->redirect('error');
        }

        call_user_func_array([$cb, $func], $route['arguments']);
    }

    public function redirect($url) {
        header('Location: ' . WEB_LINK . $url);
    }

    private function buildRoutes() {
        foreach (self::$routes as $key => $route) {
            $tmp = [];
            foreach ($route['arguments'] as $var => $regex) {
                $tmp[0][] = ':' . $var;
                $tmp[1][] = '(?P<' . $var . '>' . $regex . ')';
            }
            self::$routes[$route['name']] = self::$routes[$key];
            unset(self::$routes[$key]);
            self::$routes[$route['name']]['route'] = str_replace($tmp[0], $tmp[1], $route['url']);
        }
    }

    public static function getRoute($name) {
        if (isset(self::$routes[$name])) {
            return self::$routes[$name];
        }
        return [
            'name' => '404',
            'url' => '/404'
        ];
    }
}