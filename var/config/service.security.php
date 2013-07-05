<?php
// Security specifications

return array(
    // IP check: deny 'bad' IPs, approve 'good' IPs
    'ip'    => array(
        'bad'           => '',
        'good'          => '^127.0|^10.0',
        'checkProxy'    => true,
    ),

    // Super GLOBALS
    'globals' => array('GLOBALS', '_SESSION', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES'),

    // XSS check
    'xss'   => array(
        'post'      => true,
        'get'       => true,
        'filter'    => true,
        'length'    => 32,
    ),

    // crawl bots protection on HTTP_USER_AGENT
    'bot' => array('bad bot', 'evil bot'),

    // Both DoS and bot proection on HTTP_USER_AGENT
    // Note: disable above 'dos' and 'bot' if the below option is enabled
    'agent' => array(
        'dos' => true,
        'bot' => array('bad bot', 'evil bot'),
    ),
);
