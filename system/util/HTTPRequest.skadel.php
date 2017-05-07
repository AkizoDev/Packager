<?php


namespace skadel\system\util;


class HTTPRequest {

    private $postParameter = [];
    private $headers = [];
    private $host = null;
    private $method = 'GET';

    private $response = null;
    private $responseHeader = [];
    private $attachedFile = null;

    public function __construct($host, $method = 'GET') {
        $this->host = $host;
        $this->method = $method;

        $this->addHeader('User-Agent', INTEGRATION_NAME);
        $this->addHeader('Content-Type', 'application/json');
    }

    public function addHeader($header, $value) {
        if ($value === '') {
            unset($this->headers[$header]);
        } else {
            $this->headers[$header] = $value;
        }
    }

    public function setPostParameter($parameters) {
        $this->postParameter = $parameters;
    }

    public function setAttachedFile($pathToFile) {
        if (is_file($pathToFile)) {
            $this->attachedFile = $pathToFile;
        } else {
            $this->attachedFile = null;
        }
    }

    public function execute() {

        $ch = curl_init($this->host);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        if (is_array($this->postParameter) && count($this->postParameter) > 0 && $this->method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->postParameter));
            $this->addHeader('Content-Length', strlen(json_encode($this->postParameter)));
        }
        if ($this->attachedFile !== null) {
            $file = file_get_contents($this->attachedFile);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            $this->addHeader('Content-Length', filesize($this->attachedFile));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [&$this, 'handleResponseHeader']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders());

        $this->response = curl_exec($ch);
    }

    public function handleResponse() {
        return [
            'header' => $this->responseHeader,
            'body' => $this->response
        ];
    }

    public function handleJsonResponse() {
        $response = $this->handleResponse();
        try {
            $response['body'] = json_decode($response['body'], true);
            return $response;
        } catch (\Exception $e) {
            return $response;
        }
    }

    protected function handleResponseHeader($ch, $header) {
        $pos = strpos($header, ':');
        if ($pos !== false) {
            $this->responseHeader[substr($header, 0, $pos)] = rtrim(substr(strstr($header, ':'), 2));
        }

        return strlen($header);
    }

    protected function formatHeaders() {
        $tmp = [];
        foreach ($this->headers as $header => $value) {
            $tmp[] = $header . ': ' . $value;
        }
        return $tmp;
    }
}