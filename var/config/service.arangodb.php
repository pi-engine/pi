<?php
/**
 *  arangoDB service configuration
 */

return [
    // Set service active or inactive by true / false
    'active'             => false,

    // database name
    'database'           => '_system',

    // server endpoint to connect to
    'endpoint'           => 'tcp://127.0.0.1:8529',

    // authorization type to use (currently supported: 'Basic')
    'authorization_type' => 'Basic',

    // user for basic authorization
    'user'               => 'root',

    // password for basic authorization
    'password'           => '',

    // connection persistence on server. can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
    'connection'         => 'Close',

    // connect timeout in seconds
    'timeout'            => 3,

    // whether or not to reconnect when a keep-alive connection has timed out on server
    'reconnect'          => true,

    // optionally create new collections when inserting documents
    'create'             => true,
];
