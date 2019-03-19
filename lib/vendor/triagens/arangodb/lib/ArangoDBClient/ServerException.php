<?php

/**
 * ArangoDB PHP client: server exception
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Server-Exception
 *
 * This exception type will be thrown by the client when the server returns an
 * error in response to a client request.
 *
 * The exception code is the HTTP status code as returned by
 * the server.
 * In case the server provides additional details
 * about the error, these details can be queried using the
 * getDetails() function.<br>
 * <br>
 *
 * @package ArangoDBClient
 * @since   0.2
 */
class ServerException extends Exception
{
    /**
     * Optional details for the exception
     *
     * @param array
     */
    private $_details = [];

    /**
     * Error number index
     */
    const ENTRY_CODE = 'errorNum';

    /**
     * Error message index
     */
    const ENTRY_MESSAGE = 'errorMessage';

    /**
     * Return a string representation of the exception
     *
     * @return string - string representation
     */
    public function __toString()
    {
        return __CLASS__ . ': ' . $this->getServerCode() . ' ' . $this->getMessage();
    }

    /**
     * Set exception details
     *
     * If the server provides additional details about the error
     * that occurred, they will be put here.
     *
     * @param array $details - array of exception details
     *
     * @return void
     */
    public function setDetails(array $details)
    {
        $this->_details = $details;
    }

    /**
     * Get exception details
     *
     * If the server has provided additional details about the error
     * that occurred, they can be queries using the method
     *
     * @return array - array of details
     */
    public function getDetails()
    {
        return $this->_details;
    }

    /**
     * Get server error code
     *
     * If the server has provided additional details about the error
     * that occurred, this will return the server error code
     *
     * @return int - server error code
     */
    public function getServerCode()
    {
        if (isset($this->_details[self::ENTRY_CODE])) {
            return $this->_details[self::ENTRY_CODE];
        }

        return $this->getCode();
    }

    /**
     * Get server error message
     *
     * If the server has provided additional details about the error
     * that occurred, this will return the server error string
     *
     * @return string - server error message
     */
    public function getServerMessage()
    {
        if (isset($this->_details[self::ENTRY_MESSAGE])) {
            return $this->_details[self::ENTRY_MESSAGE];
        }

        return null;
    }
}

class_alias(ServerException::class, '\triagens\ArangoDb\ServerException');
