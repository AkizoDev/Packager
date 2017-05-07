<?php

namespace skadel\system\controller;

use skadel\system\util\discord\Payload;
use skadel\system\util\Response;


class Webhook {
    private $payload = null;

    public function execute($secret1, $secret2, $provider) {
        $tmp = '\skadel\provider\\' . ucfirst($provider);
        if (class_exists($tmp)) {
            $this->payload = new Payload([$secret1, $secret2]);
            $parser = new $tmp();
            $req = new Response(file_get_contents('php://input'));
            $parser->parse($req, $this->payload);
            $this->payload->send();
            return;
        }
        Response::sendStatus(404);
    }

    public function executeCanary($secret1, $secret2, $provider) {
        $tmp = '\skadel\provider\\' . ucfirst($provider);
        if (class_exists($tmp)) {
            $this->payload = new Payload([$secret1, $secret2], true);
            $parser = new $tmp();
            $req = new Response(file_get_contents('php://input'));
            $parser->parse($req, $this->payload);
            $this->payload->send();
            return;
        }
        Response::sendStatus(404);
    }
}