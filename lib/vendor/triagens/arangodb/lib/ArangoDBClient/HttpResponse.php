<?php

/**
 * ArangoDB PHP client: HTTP response
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Container class for HTTP responses
 *
 * <br>
 *
 * @package ArangoDBClient
 * @since   0.2
 */
class HttpResponse
{
    /**
     * The header retrieved
     *
     * @var string
     */
    private $_header = '';

    /**
     * The body retrieved
     *
     * @var string
     */
    private $_body = '';

    /**
     * All headers retrieved as an assoc array
     *
     * @var array
     */
    private $_headers = [];

    /**
     * The result status-line (first line of HTTP response header)
     *
     * @var string
     */
    private $_result = '';

    /**
     * The HTTP status code of the response
     *
     * @var int
     */
    private $_httpCode;

    /**
     * Whether or not the response is for an async request without a response body
     *
     * @var bool
     */
    private $_wasAsync = false;

    /**
     * Whether or not the response is for an async request without a response body
     *
     * @var Batchpart
     */
    private $batchPart;

    /**
     * HTTP location header
     */
    const HEADER_LOCATION = 'location';
    
    /**
     * HTTP leader endpoint header, used in failover
     */
    const HEADER_LEADER_ENDPOINT = 'x-arango-endpoint';

    /**
     * Set up the response
     *
     *
     * @param string $responseString - the complete HTTP response as supplied by the server
     * @param string $originUrl      The original URL the response is coming from
     * @param string $originMethod   The HTTP method that was used when sending data to the origin URL
     *
     * @param bool   $wasAsync
     *
     * @throws ClientException
     */
    public function __construct($responseString, $originUrl = null, $originMethod = null, $wasAsync = false)
    {
        $this->_wasAsync = $wasAsync;
        if ($originUrl !== null && $originMethod !== null) {
            if ($responseString === '') {
                throw new ClientException(
                    'Got no response from the server after request to '
                    . $originMethod . ' ' . $originUrl . ' - Note: this may be a timeout issue'
                );
            }
        }

        list($this->_header, $this->_body) = HttpHelper::parseHttpMessage($responseString, $originUrl, $originMethod);
        list($this->_httpCode, $this->_result, $this->_headers) = HttpHelper::parseHeaders($this->_header);

        if ($this->_body === null &&
            ($this->_httpCode !== 204 && $this->_httpCode !== 304 && !$wasAsync)
        ) {
            // got no response body!
            if ($originUrl !== null && $originMethod !== null) {
                throw new ClientException(
                    'Got an invalid response from the server after request to '
                    . $originMethod . ' ' . $originUrl
                );
            }
            throw new ClientException('Got an invalid response from the server');
        }
    }

    /**
     * Return the HTTP status code of the response
     *
     * @return int - HTTP status code of response
     */
    public function getHttpCode()
    {
        return $this->_httpCode;
    }

    /**
     * Return an individual HTTP headers of the response
     *
     * @param string $name - name of header
     *
     * @return string - header value, NULL if header wasn't set in response
     */
    public function getHeader($name)
    {
        assert(is_string($name));

        $name = strtolower($name);

        if (isset($this->_headers[$name])) {
            return $this->_headers[$name];
        }

        return null;
    }

    /**
     * Return the HTTP headers of the response
     *
     * @return array - array of all headers with values
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Return the location HTTP header of the response
     *
     * @return string - header value, NULL is header wasn't set in response
     */
    public function getLocationHeader()
    {
        return $this->getHeader(self::HEADER_LOCATION);
    }
    
    /**
     * Return the leader location HTTP header of the response
     *
     * @return string - header value, NULL is header wasn't set in response
     */
    public function getLeaderEndpointHeader()
    {
        return $this->getHeader(self::HEADER_LEADER_ENDPOINT);
    }

    /**
     * Return the body of the response
     *
     * @return string - body of the response
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Return the result line (first header line) of the response
     *
     * @return string - the result line (first line of header)
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Return the data from the JSON-encoded body
     *
     * @throws ClientException
     * @return array - array of values from the JSON-encoded response body
     */
    public function getJson()
    {
        $body = $this->getBody();
        $json = json_decode($body, true);

        if (!is_array($json)) {
            if ($this->_wasAsync) {
                return [];
            }

            // should be an array, fail otherwise
            throw new ClientException('Got a malformed result from the server');
        }

        return $json;
    }

    /**
     * @param Batchpart $batchPart
     *
     * @return HttpResponse
     */
    public function setBatchPart($batchPart)
    {
        $this->batchPart = $batchPart;

        return $this;
    }

    /**
     * @return Batchpart
     */
    public function getBatchPart()
    {
        return $this->batchPart;
    }

}

class_alias(HttpResponse::class, '\triagens\ArangoDb\HttpResponse');
