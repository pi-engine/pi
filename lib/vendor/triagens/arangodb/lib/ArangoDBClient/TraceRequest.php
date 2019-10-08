<?php
/**
 * ArangoDB PHP client: connection
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @author    Francis Chuang
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Class TraceRequest
 *
 * @author    Francis Chuang
 * @package   ArangoDBClient
 * @since     1.3
 */
class TraceRequest
{
    /**
     * Stores each header as an array (key => value) element
     *
     * @var array
     */
    private $_headers = [];

    /**
     * Stores the http method
     *
     * @var string
     */
    private $_method;

    /**
     * Stores the request url
     *
     * @var string
     */
    private $_requestUrl;

    /**
     * Store the string of the body
     *
     * @var string
     */
    private $_body;

    /**
     * The http message type
     *
     * @var string
     */
    private $_type = 'request';

    /**
     * Set up the request trace
     *
     * @param array  $headers    - the array of http headers
     * @param string $method     - the request method
     * @param string $requestUrl - the request url
     * @param string $body       - the string of http body
     */
    public function __construct($headers, $method, $requestUrl, $body)
    {
        $this->_headers    = $headers;
        $this->_method     = $method;
        $this->_requestUrl = $requestUrl;
        $this->_body       = $body;
    }

    /**
     * Get an array of the request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Get the request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Get the request url
     *
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->_requestUrl;
    }

    /**
     * Get the body of the request
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Get the http message type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
}

class_alias(TraceRequest::class, '\triagens\ArangoDb\TraceRequest');
