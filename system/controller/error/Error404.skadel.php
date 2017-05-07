<?php

namespace skadel\system\controller\error;


use skadel\system\util\Template;

class Error404 {
    public function display(){
        Template::displayError(404);
    }
}