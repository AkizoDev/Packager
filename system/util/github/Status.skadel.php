<?php


namespace skadel\system\util\github;


use skadel\system\util\HTTPRequest;

class Status {

    const STATE_PENDING = 'pending';
    const STATE_FAILED = 'failure';
    const STATE_ERROR = 'error';
    const STATE_SUCCESS = 'success';

    private $token = null;
    private $repository = null;
    private $commit = null;

    /**
     * Status constructor.
     * @param string $token      API access token
     * @param string $repository Repository information (owner/name)
     * @param string $commit     commit hash
     */
    public function __construct($token, $repository, $commit) {
        $this->token = $token;
        $this->repository = $repository;
        $this->commit = $commit;
    }

    public function create($state, $description) {
        if ($state !== '' && $description !== '' && $this->token !== null && $this->repository !== null && $this->commit !== null) {
            $createStatus = new HTTPRequest('https://api.github.com/repos/' . $this->repository . '/statuses/' . $this->commit, 'POST');
            $createStatus->addHeader('Authorization', 'Token ' . $this->token);
            $createStatus->addHeader('Accept', 'application/vnd.github.v3+json');
            $createStatus->setPostParameter([
                'state' => $state,
                'description' => $description,
                'context' => 'deployment/' . str_replace(' ', '-', WEB_TITLE) . 'Packager'
            ]);
            $createStatus->execute();
        }
    }


}