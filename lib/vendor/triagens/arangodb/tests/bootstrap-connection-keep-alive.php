<?php
/**
 * ArangoDB PHP client testsuite
 * File: bootstrap-connection-close.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/bootstrap.php';

if (class_exists(\PHPUnit\Framework\TestCase::class)) {
    @class_alias(\PHPUnit\Framework\TestCase::class, 'PHPUnit_Framework_TestCase');
}

function getConnectionOptions() {
    return array_merge(getConnectionOptionsGlobal(), [
        ConnectionOptions::OPTION_CONNECTION         => 'Keep-Alive'
        // can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
    ]);
}
