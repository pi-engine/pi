<?php
// Security specifications

return array(
    // IP check: deny 'bad' IPs, approve 'good' IPs
    'ip'        => true,

    // Super GLOBALS
    'globals'   => true,

    // XSS check
    'xss'       => true,

    // Enable DoS protection on HTTP_USER_AGENT
    'dos'       => true,

    // crawl bots protection on HTTP_USER_AGENT
    'bot'       => true,

    // Both DoS and bot proection on HTTP_USER_AGENT
    // Note: disable above 'dos' and 'bot' if the below option is enabled
    'agent'     => true,
);
