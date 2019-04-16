<?php

/**
 * ArangoDB PHP client: update policies
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Document update policies
 *
 * @package ArangoDBClient
 * @since   0.2
 */
class UpdatePolicy
{
    /**
     * last update will win in case of conflicting versions
     */
    const LAST = 'last';

    /**
     * an error will be returned in case of conflicting versions
     */
    const ERROR = 'error';

    /**
     * Check if the supplied policy value is valid
     *
     * @throws ClientException
     *
     * @param string $value - update policy value
     *
     * @return void
     */
    public static function validate($value)
    {
        assert(is_string($value));

        if ($value !== self::LAST && $value !== self::ERROR) {
            throw new ClientException('Invalid update policy');
        }
    }
}

class_alias(UpdatePolicy::class, '\triagens\ArangoDb\UpdatePolicy');
