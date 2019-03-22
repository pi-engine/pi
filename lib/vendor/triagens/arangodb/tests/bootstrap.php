<?php
/**
 * ArangoDB PHP client testsuite
 * File: bootstrap-connection-close.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

function isCluster(Connection $connection)
{
    static $isCluster = null;

    if ($isCluster === null) {
        $adminHandler = new AdminHandler($connection);
        try {
            $role      = $adminHandler->getServerRole();
            $isCluster = ($role === 'COORDINATOR' || $role === 'DBSERVER');
        } catch (\Exception $e) {
            // maybe server version is too "old"
            $isCluster = false;
        }
    }

    return $isCluster;
}

function useAuthentication() {
    $authentication = getenv("ARANGO_USE_AUTHENTICATION");
    
    if ($authentication === false) {
        // use ArangoDB default value
        return true;
    }

    $authentication = strtolower($authentication);

    if ($authentication === '0' || $authentication === 'false' || 
        $authentication === 'off' || $authentication === '') {
        return false;
    }

    return true;
}

function getConnectionOptionsGlobal()
{
    $traceFunc = function ($type, $data) {
        print 'TRACE FOR ' . $type . PHP_EOL;
    };

    $host = getenv("ARANGO_HOST");
    if ($host === false) {
      $host = "localhost"; // default host
    }
    
    $port = getenv("ARANGO_PORT");
    if ($port === false) {
        $port = "8529"; // default port
    }
    
    $passwd = getenv("ARANGO_ROOT_PASSWORD");
    if ($passwd === false) {
        $passwd = ""; // default root password
    }
    
    return [
        ConnectionOptions::OPTION_ENDPOINT           => 'tcp://' . $host . ':' . $port,
        // endpoint to connect to
        ConnectionOptions::OPTION_AUTH_TYPE          => 'Basic',
        // use basic authorization
        ConnectionOptions::OPTION_AUTH_USER          => 'root',
        // user for basic authorization
        ConnectionOptions::OPTION_AUTH_PASSWD        => $passwd,
        // password for basic authorization
        ConnectionOptions::OPTION_TIMEOUT            => 60,
        // timeout in seconds
        //ConnectionOptions::OPTION_TRACE       => $traceFunc,              // tracer function, can be used for debugging
        ConnectionOptions::OPTION_CREATE             => false,
        // do not create unknown collections automatically
        ConnectionOptions::OPTION_UPDATE_POLICY      => UpdatePolicy::LAST,
        // last update wins
        ConnectionOptions::OPTION_CHECK_UTF8_CONFORM => true
        // force UTF-8 checks for data
    ];
}

function getConnection()
{
    return new Connection(getConnectionOptions());
}
