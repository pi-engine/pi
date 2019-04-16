<?php

namespace ArangoDBClient;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';

/* set up a trace function that will be called for each communication with the server */
$traceFunc = function ($type, $data) {
    print 'TRACE FOR ' . $type . PHP_EOL;
    var_dump($data);
};

/* set up connection options */
$connectionOptions = [
    ConnectionOptions::OPTION_DATABASE => '_system',               // database name

    // normal unencrypted connection via TCP/IP
    ConnectionOptions::OPTION_ENDPOINT => 'tcp://localhost:8529',  // endpoint to connect to
    
    // // to use failover (requires ArangoDB 3.3 and the database running in active/passive failover mode)
    // // it is possible to specify an array of endpoints as follows:
    // ConnectionOptions::OPTION_ENDPOINT    => [ 'tcp://localhost:8531', 'tcp://localhost:8532' ]
    
    // // to use memcached for caching the currently active leader (to spare a few connection attempts
    // // to followers), it is possible to install the Memcached module for PHP and set the following options:
    // // memcached persistent id (will be passed to Memcached::__construct)
    // ConnectionOptions::OPTION_MEMCACHED_PERSISTENT_ID => 'arangodb-php-pool',
    // // memcached servers to connect to (will be passed to Memcached::addServers)
    // ConnectionOptions::OPTION_MEMCACHED_SERVERS       => [ [ '127.0.0.1', 11211 ] ],
    // // memcached options (will be passed to Memcached::setOptions)
    // ConnectionOptions::OPTION_MEMCACHED_OPTIONS       => [ ],
    // // key to store the current endpoints array under
    // ConnectionOptions::OPTION_MEMCACHED_ENDPOINTS_KEY => 'arangodb-php-endpoints'
    // // time-to-live for the endpoints array stored in memcached
    // ConnectionOptions::OPTION_MEMCACHED_TTL           => 600

    // // connection via SSL
    // ConnectionOptions::OPTION_ENDPOINT        => 'ssl://localhost:8529',  // SSL endpoint to connect to
    // ConnectionOptions::OPTION_VERIFY_CERT     => false,                   // SSL certificate validation
    // ConnectionOptions::OPTION_ALLOW_SELF_SIGNED => true,                  // allow self-signed certificates
    // ConnectionOptions::OPTION_CIPHERS         => 'DEFAULT',               // https://www.openssl.org/docs/manmaster/apps/ciphers.html

    // // connection via UNIX domain socket
    // ConnectionOptions::OPTION_ENDPOINT        => 'unix:///tmp/arangodb.sock',  // UNIX domain socket

    ConnectionOptions::OPTION_CONNECTION  => 'Keep-Alive',            // can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
    ConnectionOptions::OPTION_AUTH_TYPE   => 'Basic',                 // use basic authorization

    // authentication parameters (note: must also start server with option `--server.disable-authentication false`)
    ConnectionOptions::OPTION_AUTH_USER   => 'root',                  // user for basic authorization
    ConnectionOptions::OPTION_AUTH_PASSWD => '',                      // password for basic authorization

    ConnectionOptions::OPTION_TIMEOUT       => 30,                      // timeout in seconds
    ConnectionOptions::OPTION_TRACE         => $traceFunc,              // tracer function, can be used for debugging
    ConnectionOptions::OPTION_CREATE        => false,                   // do not create unknown collections automatically
    ConnectionOptions::OPTION_UPDATE_POLICY => UpdatePolicy::LAST,      // last update wins
];
