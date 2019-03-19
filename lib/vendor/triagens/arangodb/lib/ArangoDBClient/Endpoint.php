<?php

/**
 * ArangoDB PHP client: endpoint
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Endpoint specification
 *
 * An endpoint contains the server location the client connects to
 * the following endpoint types are currently supported (more to be added later):
 * <ul>
 * <li> tcp://host:port for tcp connections
 * <li> unix://socket for UNIX sockets (provided the server supports this)
 * <li> ssl://host:port for SSL connections (provided the server supports this)
 * </ul>
 *
 * Note: SSL support is added in ArangoDB server 1.1<br>
 *
 * <br>
 *
 * @package   ArangoDBClient
 * @since     0.2
 */
class Endpoint
{
    /**
     * Current endpoint value
     *
     * @var string
     */
    private $_value;

    /**
     * TCP endpoint type
     */
    const TYPE_TCP = 'tcp';

    /**
     * SSL endpoint type
     */
    const TYPE_SSL = 'ssl';

    /**
     * UNIX socket endpoint type
     */
    const TYPE_UNIX = 'unix';

    /**
     * Regexp for TCP endpoints
     */
    const REGEXP_TCP = '/^(tcp|http):\/\/(.+?):(\d+)\/?$/';

    /**
     * Regexp for SSL endpoints
     */
    const REGEXP_SSL = '/^(ssl|https):\/\/(.+?):(\d+)\/?$/';

    /**
     * Regexp for UNIX socket endpoints
     */
    const REGEXP_UNIX = '/^unix:\/\/(.+)$/';

    /**
     * Endpoint index
     */
    const ENTRY_ENDPOINT = 'endpoint';

    /**
     * Databases index
     */
    const ENTRY_DATABASES = 'databases';


    /**
     * Create a new endpoint
     *
     * @param string $value - endpoint specification
     *
     * @throws ClientException
     *
     */
    public function __construct($value)
    {
        if (!self::isValid($value)) {
            throw new ClientException(sprintf("invalid endpoint specification '%s'", $value));
        }

        $this->_value = $value;
    }

    /**
     * Return a string representation of the endpoint
     *
     * @magic
     *
     * @return string - string representation of the endpoint
     */
    public function __toString()
    {
        return $this->_value;
    }

    /**
     * Return the type of an endpoint
     *
     * @param string $value - endpoint specification value
     *
     * @return string - endpoint type
     */
    public static function getType($value)
    {
        if (preg_match(self::REGEXP_TCP, $value)) {
            return self::TYPE_TCP;
        }

        if (preg_match(self::REGEXP_SSL, $value)) {
            return self::TYPE_SSL;
        }

        if (preg_match(self::REGEXP_UNIX, $value)) {
            return self::TYPE_UNIX;
        }

        return null;
    }
    
    
    /**
     * Return normalize an endpoint string - will convert http: into tcp:, and https: into ssl:
     *
     * @param string $value - endpoint string
     *
     * @return string - normalized endpoint string
     */
    public static function normalize($value)
    {
        return preg_replace([ "/http:/", "/https:/" ], [ "tcp:", "ssl:" ], $value);
    }

    /**
     * Return the host name of an endpoint
     *
     * @param string $value - endpoint specification value
     *
     * @return string - host name
     */
    public static function getHost($value)
    {
        if (preg_match(self::REGEXP_TCP, $value, $matches)) {
            return preg_replace("/^http:/", "tcp:", $matches[2]);
        }

        if (preg_match(self::REGEXP_SSL, $value, $matches)) {
            return preg_replace("/^https:/", "ssl:", $matches[2]);
        }

        return null;
    }

    /**
     * check whether an endpoint specification is valid
     *
     * @param string $mixed - endpoint specification value (can be a string or an array of strings)
     *
     * @return bool - true if endpoint specification is valid, false otherwise
     */
    public static function isValid($value)
    {
        if (is_string($value)) {
            $value = [ $value ];
        }
        
        if (!is_array($value) || count($value) === 0) {
            return false;
        }

        foreach ($value as $ep) {
            if (!is_string($ep)) {
                return false;
            }
            $type = self::getType($ep);

            if ($type === null) {
                return false;
            }
        }
        return true;
    }


    /**
     * List endpoints
     *
     * This will list the endpoints that are configured on the server
     *
     * @param Connection $connection - the connection to be used
     *
     * @link https://docs.arangodb.com/HTTP/Endpoints/index.html
     * @return array $responseArray - The response array.
     * @throws \ArangoDBClient\Exception
     */
    public static function listEndpoints(Connection $connection)
    {
        $response = $connection->get(Urls::URL_ENDPOINT);

        return $response->getJson();
    }
    
    /**
     * Replaces "localhost" in hostname with "[::1]" in order to make these values the same
     * for later comparisons
     *
     * @param string $hostname - hostname 
     *
     * @return string - normalized hostname
     */
    public static function normalizeHostname($hostname) {
        // replace "localhost" with [::1] as arangod does
        return preg_replace("/^(tcp|ssl|https?):\/\/(localhost|127\.0\.0\.1):/", "\\1://[::1]:",  $hostname);
    }
    
}

class_alias(Endpoint::class, '\triagens\ArangoDb\Endpoint');
