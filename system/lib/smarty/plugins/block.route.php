<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsBlock
 */
use skadel\system\util\Routing;

/**
 * Smarty {route}{/route} block plugin
 *
 * Type:     block function<br>
 * Name:     route<br>
 *
 * @param array $params parameters
 * @param string $content contents of the block
 * @param Smarty_Internal_Template $template template object
 * @param boolean &$repeat repeat flag
 * @return string content re-formatted
 * @author Tobias 'Mythen' Klein <contact@isekaidev.de>
 */
function smarty_block_route($params, $content, $template, &$repeat) {
    if (is_null($content)) {
        return null;
    }

    $route = Routing::getRoute($content);

    if (isset($route['url'])) {
        if (isset($params['options'])) {
            foreach (json_decode($params['options'], true) as $key => $item) {
                $route['url'] = preg_replace('#:' . $key . '#U', $item, $route['url']);
            }
        } else {
            foreach ($params as $key => $item) {
                $route['url'] = preg_replace('#:' . $key . '#U', $item, $route['url']);
            }
        }
        return WEB_LINK . substr($route['url'], 1);
    } else {
        return WEB_LINK . substr($route['url'], 1);
    }
}
