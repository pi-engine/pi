<?php

/**
 * ArangoDB PHP client: URL helper methods
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Some helper methods to construct and process URLs
 *
 * @package ArangoDBClient
 * @since   0.2
 */
abstract class UrlHelper
{
    /**
     * Get the document id from a location header
     *
     * @param string $location - HTTP response location header
     *
     * @return string - document id parsed from header
     */
    public static function getDocumentIdFromLocation($location)
    {
        if (!is_string($location)) {
            // can't do anything about it if location is not even a string
            return null;
        }

        if (0 === strpos($location, '/_db/')) {
            // /_db/<dbname>/_api/document/<collection>/<key>
            @list(, , , , , $collectionName, $id) = explode('/', $location);
        } else {
            // /_api/document/<collection>/<key>
            @list(, , , $collectionName, $id) = explode('/', $location);
        }

        if (is_string($id)) {
            $id = urldecode($id);
        }

        return $collectionName . '/' . $id;
    }

    /**
     * Construct a URL from a base URL and additional parts, separated with '/' each
     *
     * This function accepts variable arguments.
     *
     * @param string $baseUrl - base URL
     * @param array  $parts   - URL parts to append
     *
     * @return string - assembled URL
     */
    public static function buildUrl($baseUrl, array $parts = [])
    {
        $url = $baseUrl;

        foreach ($parts as $part) {
            if (strpos($part, '/') !== false) {
                @list(,$part) = explode('/', $part);
            }

            $url .= '/' . urlencode($part);
        }

        return $url;
    }

    /**
     * Append parameters to a URL
     *
     * Parameter values will be URL-encoded
     *
     * @param string $baseUrl - base URL
     * @param array  $params  - an array of parameters
     *
     * @return string - the assembled URL
     */
    public static function appendParamsUrl($baseUrl, $params)
    {
        foreach ($params as $key => &$value) {
            if (is_bool($value)) {
                $value = self::getBoolString($value);
            }
        }

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Get a string from a boolean value
     *
     * @param mixed $value - the value
     *
     * @return string - "true" if $value evaluates to true, "false" otherwise
     */
    public static function getBoolString($value)
    {
        return $value ? 'true' : 'false';
    }
}

class_alias(UrlHelper::class, '\triagens\ArangoDb\UrlHelper');
