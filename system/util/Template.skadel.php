<?php

namespace skadel\system\util;

require_once SYS_DIR . 'lib/smarty/Smarty.class.php';

class Template {
    private static $smarty;

    public function __construct() {
        self::$smarty = new \Smarty();

        self::$smarty->setTemplateDir(VIEW_DIR);
        self::$smarty->setCacheDir(VIEW_DIR . 'cache/');
        self::$smarty->setCompileDir(VIEW_DIR . 'cache/compiled/');

        self::$smarty->debugging = false;
        self::$smarty->caching = true;
        self::$smarty->cache_lifetime = 0; //-1

        self::$smarty->assign('webDir', WEB_LINK);

        if (date('Y') == 2017) {
            self::$smarty->assign('tplYear', date('Y'));
        } else {
            self::$smarty->assign('tplYear', '2017-' . date('Y'));
        }
    }

    public static function assign($var, $value = '') {
        if (is_array($var)) {
            foreach ($var as $key => $value) {
                if (empty($key)) {
                    continue;
                }
                self::$smarty->assign($key, $value);
            }
        } else {
            self::$smarty->assign($var, $value);
        }
    }

    public static function display($page) {
        if (file_exists(VIEW_DIR . 'page/' . $page . '.tpl')) {
            self::$smarty->display('page/' . $page . '.tpl');
        } else {
            self::$smarty->display('error/404.tpl');
        }
    }

    public static function displayError($error){
        if (file_exists(VIEW_DIR . 'error/' . $error . '.tpl')) {
            self::$smarty->display('error/' . $error . '.tpl');
        } else {
            self::$smarty->display('error/404.tpl');
        }
    }
}