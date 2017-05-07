<?php

return [
    [
        'name' => 'Dashboard',
        'method' => 'GET',
        'url' => '/',
        'controller' => ['\skadel\system\controller\Dashboard', 'display'],
        'arguments' => []
    ],
    [
        'name' => 'GitHubNotify',
        'method' => 'POST',
        'url' => '/notify',
        'controller' => ['\skadel\system\controller\GitHubIntegration', 'notify'],
        'arguments' => []
    ]
];