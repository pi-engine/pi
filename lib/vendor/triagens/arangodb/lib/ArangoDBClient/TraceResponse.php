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
 * Class TraceResponse
 *
 * @author    Francis Chuang
 * @package   ArangoDBClient
 * @since     1.3
 */
class TraceResponse
{
    /**
     * Stores each header as an array (key => value) element
     *
     * @var array
     */
    private $_headers = [];

    /**
     * The http status code
     *
     * @var int
     */
    private $_httpCode;

    /**
     * The raw body of the response
     *
     * @var string
     */
    private $_body;

    /**
     * The type of http message
     *
     * @var string
     */
    private $_type = 'response';

    /**
     * The time taken to send and receive a response in seconds
     *
     * @var float
     */
    private $_timeTaken;

    /**
     * Used to look up the definition for an http code
     *
     * @var array
     */
    private $_httpCodeDefinitions = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];

    /**
     * Set up the response trace
     *
     * @param array  $headers  - the array of http headers
     * @param int    $httpCode - the http code
     * @param string $body     - the string of http body
     * @param        $timeTaken
     */
    public function __construct($headers, $httpCode, $body, $timeTaken)
    {
        $this->_headers   = $headers;
        $this->_httpCode  = $httpCode;
        $this->_body      = $body;
        $this->_timeTaken = $timeTaken;
    }

    /**
     * Get an array of the response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Get the http response code
     *
     * @return int
     */
    public function getHttpCode()
    {
        return $this->_httpCode;
    }

    /**
     * Get the http code definition
     *
     * @throws ClientException
     * @return string
     */
    public function getHttpCodeDefinition()
    {
        if (!isset($this->_httpCodeDefinitions[$this->getHttpCode()])) {
            throw new ClientException('Invalid http code provided.');
        }

        return $this->_httpCodeDefinitions[$this->getHttpCode()];
    }

    /**
     * Get the response body
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

    /**
     * Get the time taken for this request
     */
    public function getTimeTaken()
    {
        return $this->_timeTaken;
    }
}

class_alias(TraceResponse::class, '\triagens\ArangoDb\TraceResponse');
