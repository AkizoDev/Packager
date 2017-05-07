<?php

namespace skadel\system;

use skadel\system\util\Routing;
use skadel\system\util\Template;

define('WEB_PROTOCOL', (isset($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? 'https://' : 'http://');
define('WEB_LINK', WEB_PROTOCOL . $_SERVER['HTTP_HOST'] . '/');

class Core {

    private $routingHandler;

    public function __construct($cli = false) {
        spl_autoload_register([$this, '__autoload']);

        if (!$cli) {
            new Template();

            $this->routingHandler = new Routing((include SYS_DIR . 'routes.skadel.php'));
            $route = $this->routingHandler->dispatch(str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
            $this->routingHandler->exec($route);
        }
    }

    public static function __autoload($className) {
        $namespaces = explode('\\', $className);
        if (count($namespaces) > 1) {
            $prefix = array_shift($namespaces);

            if ($prefix === '') {
                $prefix = array_shift($namespaces);
            }

            $classPath = MAIN_DIR . implode('/', $namespaces) . '.skadel.php';

            if (file_exists($classPath)) {
                require_once($classPath);
            }
        }
    }
}