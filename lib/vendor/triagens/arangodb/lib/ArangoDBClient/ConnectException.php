<?php

/**
 * ArangoDB PHP client: connect exception
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Connect-Exception
 *
 * This exception type will be thrown by the client when there is an error
 * during connecting to the server.<br>
 * <br>
 *
 * @package   ArangoDBClient
 * @since     0.2
 */
class ConnectException extends Exception
{
    /**
     * Return a string representation of the exception
     *
     * @return string - string representation
     */
    public function __toString()
    {
        return __CLASS__ . ': ' . $this->getMessage();
    }
}

class_alias(ConnectException::class, '\triagens\ArangoDb\ConnectException');
