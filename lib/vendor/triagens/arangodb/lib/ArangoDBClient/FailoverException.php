<?php

/**
 * ArangoDB PHP client: failover exception
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2018, ArangoDB GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Failover-Exception
 *
 * This exception type will be thrown internally when a failover happens
 *
 * @package ArangoDBClient
 * @since   3.3
 */
class FailoverException extends Exception
{
    /**
     * New leader endpoint
     *
     * @param string
     */
    private $_leader;
    
    /**
     * Return a string representation of the exception
     *
     * @return string - string representation
     */
    public function __toString()
    {
        return __CLASS__ . ': ' . $this->getLeader();
    }
    
    /**
     * Set the new leader endpoint
     *
     * @param string - the new leader endpoint
     *
     * @return void
     */
    public function setLeader($leader)
    {
        $this->_leader = $leader;
    }

    /**
     * Return the new leader endpoint
     *
     * @return string - new leader endpoint
     */
    public function getLeader()
    {
        return $this->_leader;
    }
}

class_alias(FailoverException::class, '\triagens\ArangoDb\FailoverException');
