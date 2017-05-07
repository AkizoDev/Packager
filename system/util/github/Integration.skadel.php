<?php


namespace skadel\system\util\github;

require_once SYS_DIR . 'lib/JWToken.php';

class Integration {

    private $jwtToken = null;

    public function __construct() {
        $key = '';
        if (is_readable(SYS_DIR . 'private-key.pem')) {
            $key = file_get_contents(SYS_DIR . 'private-key.pem');
        }

        $this->jwtToken = \JWToken::encode(['iat' => time(), 'exp' => time() + 600, 'iss' => INTEGRATION_ID], $key, 'RS256');
    }

    public function getJwtToken(){
        return $this->jwtToken;
    }
}