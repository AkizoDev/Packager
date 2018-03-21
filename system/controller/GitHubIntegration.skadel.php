<?php

namespace skadel\system\controller;


use skadel\system\exception\BuildException;
use skadel\system\util\Git;
use skadel\system\util\github\Installation;
use skadel\system\util\github\Status;
use skadel\system\util\HTTPRequest;
use skadel\system\util\Response;
use skadel\system\util\wspackage\Packager;

class GitHubIntegration {

    public function notify() {
        Response::setStatus(202);
        $resp = new Response(file_get_contents('php://input'));
        if ($this->verifyPayload($resp)) {
            if ($resp->getHeader('X-GitHub-Event') === 'push') {
                $body = $resp->getParsed();
                $installID = $body['installation']['id'];
                if (isset($body['head_commit']) && isset($body['head_commit']['id']) && $body['head_commit'] != null && preg_match("/refs\/heads\/(?P<branch>.*)/", $body['ref'], $branch) >= 1) {
                    if (preg_match("/\[wsp(=(?P<message>[^\]]+))?\]/", $body['head_commit']['message'], $match) >= 1) {
                        $installation = new Installation($installID);
                        $token = $installation->getAccessToken();

                        $status = new Status($token, $body['repository']['full_name'], $body['head_commit']['id']);
                        $status->create(Status::STATE_PENDING, 'Build pending');

                        $packagePath = PACKAGE_DIR . $body['repository']['name'];
                        $repository = Git::cloneRepository('https://x-access-token:' . $token . '@github.com/' . $body['repository']['full_name'] . '.git', $packagePath, ['-b' => $branch['branch']]);

                        try {
                            $packager = new Packager($packagePath);
                            $package = $packager->build();

                            $createRelease = new HTTPRequest('https://api.github.com/repos/' . $body['repository']['full_name'] . '/releases', 'POST');
                            $createRelease->addHeader('Authorization', 'Token ' . $token);
                            $createRelease->addHeader('Accept', 'application/vnd.github.v3+json');
                            $createRelease->setPostParameter([
                                'tag_name' => str_replace(' ', '-', $package['version']) . '-' . $branch['branch'],
                                'target_commitish' => $body['head_commit']['id'],
                                'name' => 'Version ' . $package['version'] . ' (' . $branch['branch'] . ')',
                                'body' => (isset($match['message']) && strlen($match['message']) > 0) ? $match['message'] : 'Release of version ' . $package['version'],
                                'draft' => false,
                                'prerelease' => (preg_match("/^(.*?(\b(beta|alpha)\b)[^$]*)$/i", $package['version']) >= 1) ? true : false
                            ]);
                            $createRelease->execute();

                            $releaseResponse = $createRelease->handleJsonResponse();
                            $uploadUrl = str_replace('{?name,label}', '', $releaseResponse['body']['upload_url']);

                            $uploadFile = new HTTPRequest($uploadUrl . '?name=' . $package['name'] . '.tar', 'POST');
                            $uploadFile->addHeader('Authorization', 'Token ' . $token);
                            $uploadFile->addHeader('Accept', 'application/vnd.github.v3+json');
                            $uploadFile->addHeader('Content-Type', 'application/gzip');
                            $uploadFile->setAttachedFile($package['file']);
                            $uploadFile->execute();

                            $status->create(Status::STATE_SUCCESS, 'Version ' . $package['version'] . ' released');
                        } catch (BuildException $e) {
                            $status->create(Status::STATE_FAILED, $e->getMessage());
                        } catch (\Exception $e) {
                            $status->create(Status::STATE_ERROR, 'system error while building');
                        }

                        //cleanup
                        $repository->removeLocalRepo();
                    }
                }
            } else {
                Response::sendStatus(200);
            }
        } else {
            Response::sendStatus(401);
        }
    }

    /**
     * @param Response $resp
     * @return bool
     */
    private function verifyPayload($resp) {
        $signature = 'sha1=' . hash_hmac('SHA1', $resp->getRaw(), INTEGRATION_WEBHOOK_SECRET);
        return ($signature === $resp->getHeader('X-Hub-Signature'));
    }
}