<?php

/**
 * ArangoDB PHP client: http helper methods
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Helper methods for HTTP request/response handling
 *
 * @package ArangoDBClient
 * @since   0.2
 */
class HttpHelper
{
    /**
     * HTTP POST string constant
     */
    const METHOD_POST = 'POST';

    /**
     * HTTP PUT string constant
     */
    const METHOD_PUT = 'PUT';

    /**
     * HTTP DELETE string constant
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * HTTP GET string constant
     */
    const METHOD_GET = 'GET';

    /**
     * HTTP HEAD string constant
     */
    const METHOD_HEAD = 'HEAD';

    /**
     * HTTP PATCH string constant
     */
    const METHOD_PATCH = 'PATCH';

    /**
     * Chunk size (number of bytes processed in one batch)
     */
    const CHUNK_SIZE = 8192;

    /**
     * End of line mark used in HTTP
     */
    const EOL = "\r\n";

    /**
     * Separator between header and body
     */
    const SEPARATOR = "\r\n\r\n";

    /**
     * HTTP protocol version used, hard-coded to version 1.1
     */
    const PROTOCOL = 'HTTP/1.1';

    /**
     * Create a one-time HTTP connection by opening a socket to the server
     *
     * It is the caller's responsibility to close the socket
     *
     * @throws ConnectException
     *
     * @param ConnectionOptions $options - connection options
     *
     * @return resource - socket with server connection, will throw when no connection can be established
     */
    public static function createConnection(ConnectionOptions $options)
    {
        $endpoint = $options->getCurrentEndpoint();
        $context = stream_context_create();

        if (Endpoint::getType($endpoint) === Endpoint::TYPE_SSL) {
            // set further SSL options for the endpoint
            stream_context_set_option($context, 'ssl', 'verify_peer', $options[ConnectionOptions::OPTION_VERIFY_CERT]);
            @stream_context_set_option($context, 'ssl', 'verify_peer_name', $options[ConnectionOptions::OPTION_VERIFY_CERT_NAME]);
            stream_context_set_option($context, 'ssl', 'allow_self_signed', $options[ConnectionOptions::OPTION_ALLOW_SELF_SIGNED]);

            if ($options[ConnectionOptions::OPTION_CIPHERS] !== null) {
                // SSL ciphers
                stream_context_set_option($context, 'ssl', 'ciphers', $options[ConnectionOptions::OPTION_CIPHERS]);
            }
        }

        $fp = @stream_socket_client(
            Endpoint::normalize($endpoint),
            $errNo,
            $message,
            $options[ConnectionOptions::OPTION_TIMEOUT],
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$fp) {
            throw new ConnectException(
                'cannot connect to endpoint \'' .
                $endpoint . '\': ' . $message, $errNo
            );
        }

        stream_set_timeout($fp, $options[ConnectionOptions::OPTION_TIMEOUT]);

        return $fp;
    }

    /**
     * Boundary string for batch request parts
     */
    const MIME_BOUNDARY = 'XXXsubpartXXX';

    /**
     * HTTP Header for making an operation asynchronous
     */
    const ASYNC_HEADER = 'X-Arango-Async';

    /**
     * Create a request string (header and body)
     *
     * @param ConnectionOptions $options          - connection options
     * @param string            $connectionHeader - pre-assembled header string for connection
     * @param string            $method           - HTTP method
     * @param string            $url              - HTTP URL
     * @param string            $body             - optional body to post
     * @param array             $customHeaders    - any array containing header elements
     *
     * @return string - assembled HTTP request string
     * @throws ClientException
     *
     */
    public static function buildRequest(ConnectionOptions $options, $connectionHeader, $method, $url, $body, array $customHeaders = [])
    {
        if (!is_string($body)) {
            throw new ClientException('Invalid value for body. Expecting string, got ' . gettype($body));
        }

        $length = strlen($body);

        if ($options[ConnectionOptions::OPTION_BATCH] === true) {
            $contentType = 'Content-Type: multipart/form-data; boundary=' . self::MIME_BOUNDARY . self::EOL;
        } else {
            $contentType = '';

            if ($length > 0 && $options[ConnectionOptions::OPTION_BATCHPART] === false) {
                // if body is set, we should set a content-type header
                $contentType = 'Content-Type: application/json' . self::EOL;
            }
        }

        $customHeader = '';
        foreach ($customHeaders as $headerKey => $headerValue) {
            $customHeader .= $headerKey . ': ' . $headerValue . self::EOL;
        }

        // finally assemble the request
        $request = sprintf('%s %s %s', $method, $url, self::PROTOCOL) .
            $connectionHeader .   // note: this one starts with an EOL
            $customHeader .
            $contentType .
            sprintf('Content-Length: %s', $length) . self::EOL . self::EOL .
            $body;

        return $request;
    }

    /**
     * Validate an HTTP request method name
     *
     * @throws ClientException
     *
     * @param string $method - method name
     *
     * @return bool - always true, will throw if an invalid method name is supplied
     */
    public static function validateMethod($method)
    {
        if ($method === self::METHOD_POST ||
            $method === self::METHOD_PUT ||
            $method === self::METHOD_DELETE ||
            $method === self::METHOD_GET ||
            $method === self::METHOD_HEAD ||
            $method === self::METHOD_PATCH
        ) {
            return true;
        }

        throw new ClientException('Invalid request method \'' . $method . '\'');
    }

    /**
     * Execute an HTTP request on an opened socket
     *
     * It is the caller's responsibility to close the socket
     *
     * @param resource $socket  - connection socket (must be open)
     * @param string   $request - complete HTTP request as a string
     * @param string   $method  - HTTP method used (e.g. "HEAD")
     *
     * @throws ClientException
     * @return string - HTTP response string as provided by the server
     */
    public static function transfer($socket, $request, $method)
    {
        if (!is_resource($socket)) {
            throw new ClientException('Invalid socket used');
        }

        assert(is_string($request));

        @fwrite($socket, $request);
        @fflush($socket);

        $contentLength    = null;
        $expectedLength   = null;
        $totalRead        = 0;
        $contentLengthPos = 0;

        $result = '';
        $first  = true;

        while ($first || !feof($socket)) {
            $read = @fread($socket, self::CHUNK_SIZE);
            if ($read === false || $read === '') {
                break;
            }

            $totalRead += strlen($read);

            if ($first) {
                $result = $read;
                $first  = false;
            } else {
                $result .= $read;
            }

            if ($contentLength === null) {
                // check if content-length header is present

                // 12 = minimum offset (i.e. strlen("HTTP/1.1 xxx") -
                // after that we could see "content-length:"
                $pos = stripos($result, 'content-length: ', 12);

                if ($pos !== false) {
                    $contentLength    = (int) substr($result, $pos + 16, 10); // 16 = strlen("content-length: ")
                    $contentLengthPos = $pos + 17; // 17 = 16 + 1 one digit

                    if ($method === 'HEAD') {
                        // for HTTP HEAD requests, the server will respond
                        // with the proper Content-Length value, but will
                        // NOT return the body.
                        $contentLength = 0;
                    }
                }
            }

            if ($contentLength !== null && $expectedLength === null) {
                $bodyStart = strpos($result, "\r\n\r\n", $contentLengthPos);
                if ($bodyStart !== false) {
                    $bodyStart += 4; // 4 = strlen("\r\n\r\n")
                    $expectedLength = $bodyStart + $contentLength;
                }
            }

            if ($expectedLength !== null && $totalRead >= $expectedLength) {
                break;
            }
        }

        return $result;
    }

    /**
     * Splits an http message into its header and body.
     *
     * @param string $httpMessage  The http message string.
     * @param string $originUrl    The original URL the response is coming from
     * @param string $originMethod The HTTP method that was used when sending data to the origin URL
     *
     * @throws ClientException
     * @return array
     */
    public static function parseHttpMessage($httpMessage, $originUrl = null, $originMethod = null)
    {
        return explode(self::SEPARATOR, $httpMessage, 2);
    }

    /**
     * Process a string of HTTP headers into an array of header => values.
     *
     * @param string $headers - the headers string
     *
     * @return array
     */
    public static function parseHeaders($headers)
    {
        $httpCode  = null;
        $result    = null;
        $processed = [];

        foreach (explode(HttpHelper::EOL, $headers) as $lineNumber => $line) {
            if ($lineNumber === 0) {
                // first line of result is special
                if (preg_match("/^HTTP\/\d+\.\d+\s+(\d+)/", $line, $matches)) {
                    $httpCode = (int) $matches[1];
                }
                $result = $line;
            } else {
                // other lines contain key:value-like headers
                // the following is a performance optimization to get rid of
                // the two trims (which are expensive as they are executed over and over) 
                if (strpos($line, ': ') !== false) {
                    list($key, $value) = explode(': ', $line, 2);
                } else {
                    list($key, $value) = explode(':', $line, 2);
                }
                $processed[strtolower($key)] = $value;
            }
        }

        return [$httpCode, $result, $processed];
    }
}

class_alias(HttpHelper::class, '\triagens\ArangoDb\HttpHelper');
