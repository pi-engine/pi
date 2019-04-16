<?php

/**
 * ArangoDB PHP client: single vertex document
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @author    Frank Mayer
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 * @since     1.2
 */

namespace ArangoDBClient;

/**
 * Value object representing a single vertex document
 *
 * @package   ArangoDBClient
 * @since     1.2
 */
class Vertex extends Document
{

}

class_alias(Vertex::class, '\triagens\ArangoDb\Vertex');
