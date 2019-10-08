<?php

/**
 * ArangoDB PHP client: base handler
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * A base class for REST-based handlers
 *
 * @package ArangoDBClient
 * @since   0.2
 */
abstract class Handler
{
    /**
     * Import $_documentClass functionality
     */
    use DocumentClassable;

    /**
     * Connection object
     *
     * @param Connection
     */
    private $_connection;


    /**
     * Construct a new handler
     *
     * @param Connection $connection - connection to be used
     *
     */
    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Return the connection object
     *
     * @return Connection - the connection object
     */
    protected function getConnection()
    {
        return $this->_connection;
    }


    /**
     * Return a connection option
     * This is a convenience function that calls json_encode_wrapper on the connection
     *
     * @param $optionName - The option to return a value for
     *
     * @return mixed - the option's value
     * @throws \ArangoDBClient\ClientException
     */
    protected function getConnectionOption($optionName)
    {
        return $this->getConnection()->getOption($optionName);
    }


    /**
     * Return a json encoded string for the array passed.
     * This is a convenience function that calls json_encode_wrapper on the connection
     *
     * @param array $body - The body to encode into json
     *
     * @return string - json string of the body that was passed
     * @throws \ArangoDBClient\ClientException
     */
    protected function json_encode_wrapper($body)
    {
        return $this->getConnection()->json_encode_wrapper($body);
    }


    //todo: (@frankmayer) check if refactoring a bit more makes sense...
    /**
     * Helper function that runs through the options given and includes them into the parameters array given.
     * Only options that are set in $includeArray will be included.
     * This is only for options that are to be sent to the ArangoDB server in form of url parameters (like 'waitForSync', 'keepNull', etc...) .
     *
     * @param array $options      - The options array that holds the options to include in the parameters
     * @param array $includeArray - The array that defines which options are allowed to be included, and what their default value is. for example: 'waitForSync'=>true
     *
     * @return array $params - array of parameters for use in a url
     * @throws \ArangoDBClient\ClientException
     * @internal param array $params - The parameters into which the options will be included.
     */
    protected function includeOptionsInParams($options, array $includeArray = [])
    {
        $params = [];
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $includeArray)) {
                if ($key === ConnectionOptions::OPTION_UPDATE_POLICY) {
                    UpdatePolicy::validate($value);
                }
                $params[$key] = $value;
                if ($value === null) {
                    $params[$key] = $includeArray[$key];
                }
            }
        }

        return $params;
    }


    //todo: (@frankmayer) check if refactoring a bit more makes sense...
    /**
     * Helper function that runs through the options given and includes them into the parameters array given.
     * Only options that are set in $includeArray will be included.
     * This is only for options that are to be sent to the ArangoDB server in a json body(like 'limit', 'skip', etc...) .
     *
     * @param array $options      - The options array that holds the options to include in the parameters
     * @param array $body         - The array into which the options will be included.
     * @param array $includeArray - The array that defines which options are allowed to be included, and what their default value is. for example: 'waitForSync'=>true
     *
     * @return array $params - array of parameters for use in a url
     */
    protected function includeOptionsInBody($options, $body, array $includeArray = [])
    {
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $includeArray)) {
                $body[$key] = $value;
                if ($value === null && $includeArray[$key] !== null) {
                    $body[$key] = $includeArray[$key];
                }
            }
        }

        return $body;
    }

    /**
     * Turn a value into a collection name
     *
     * @throws ClientException
     *
     * @param mixed $value - document, collection or string
     *
     * @return string - collection name
     */
    protected function makeCollection($value)
    {
        if ($value instanceof Collection) {
            return $value->getName();
        }
        if ($value instanceof Document) {
            return $value->getCollectionId();
        }

        return $value;
    }

}

class_alias(Handler::class, '\triagens\ArangoDb\Handler');
