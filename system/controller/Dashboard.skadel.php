<?php

namespace skadel\system\controller;

use skadel\system\util\Template;

class Dashboard {

    public function display() {
        Template::display('dashboard');
    }
}