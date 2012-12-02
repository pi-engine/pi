<?php
// Security specifications

return array(
    // IP check: deny 'bad' IPs, approve 'good' IPs
    'ip'    => array(
        //'bad'           => '^127.0|^10.0',
        'good'          => '^127.0|^10.0',
        'checkProxy'    => true,
    ),

    // Super GLOBALS
    'globals' => 'GLOBALS, _SESSION, _GET, _POST, _COOKIE, _REQUEST, _SERVER, _ENV, _FILES',

    // XSS check
    'xss'   => array(
        'post'      => true,
        'get'       => true,
        'filter'    => 1,
        'length'    => 32,
    ),

    // Enable DoS protection on HTTP_USER_AGENT
    'dos' => 1,

    // crawl bots protection on HTTP_USER_AGENT
    'bot' => 'bad bot|evil bot',

    // Both DoS and bot proection on HTTP_USER_AGENT
    // Note: disable above 'dos' and 'bot' if the below option is enabled
    'agent' => array(
        'dos' => 1,
        'bot' => 'bad bot|evil bot',
    ),
);
