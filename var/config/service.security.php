<?php
// Security specifications

return [
    // IP check: deny 'bad' IPs, approve 'good' IPs
    'ip'      => [
        'bad'        => '',
        'good'       => '^127.0|^10.0',
        'checkProxy' => true,
    ],

    // Super GLOBALS
    'globals' => ['GLOBALS', '_SESSION', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES'],

    // XSS check
    'xss'     => [
        'post'   => true,
        'get'    => true,
        'filter' => true,
        'length' => 32,
    ],

    // crawl bots protection on HTTP_USER_AGENT
    'bot'     => ['bad bot', 'evil bot'],

    // Both DoS and bot proection on HTTP_USER_AGENT
    // Note: disable above 'dos' and 'bot' if the below option is enabled
    'agent'   => [
        'dos' => true,
        'bot' => ['bad bot', 'evil bot'],
    ],
];
