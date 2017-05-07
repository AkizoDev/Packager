<?php
ini_set('display_errors', true);
error_reporting(true);
date_default_timezone_set('Europe/Berlin');

session_start();
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', time()));
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time()));
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

define('MAIN_DIR', dirname(__FILE__) . '/');
define('SYS_DIR', MAIN_DIR . 'system/');
define('VIEW_DIR', MAIN_DIR . 'view/');
define('PACKAGE_DIR', MAIN_DIR . 'packages/');

require_once SYS_DIR . 'config.skadel.php';
require_once SYS_DIR . 'Core.skadel.php';

new \skadel\system\Core((php_sapi_name() === 'cli' OR defined('STDIN')));