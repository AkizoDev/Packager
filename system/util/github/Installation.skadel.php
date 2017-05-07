<?php


namespace skadel\system\util\github;


use skadel\system\util\HTTPRequest;

class Installation extends Integration {

    private $id = 0;
    private $accessToken = null;
    private $tokenExpire = 0;

    public function __construct($installationID) {
        parent::__construct();

        $this->id = $installationID;
    }

    public function getAccessToken() {
        if ($this->accessToken === null || $this->tokenExpire <= time()) {
            $request = new HTTPRequest('https://api.github.com/installations/' . $this->id . '/access_tokens', 'POST');
            $request->addHeader('Authorization', 'Bearer ' . $this->getJwtToken());
            $request->addHeader('Accept', 'application/vnd.github.machine-man-preview+json');
            $request->execute();

            $response = $request->handleJsonResponse();

            $this->accessToken = $response['body']['token'];
            $this->tokenExpire = strtotime($response['body']['expires_at']);
        }

        return $this->accessToken;
    }
}