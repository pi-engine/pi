<?php

/**
 * ArangoDB PHP client: vertex document handler
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @author    Frank Mayer
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 * @since     1.2
 */

namespace ArangoDBClient;

/**
 * A handler that manages vertices.
 * A vertex-document handler that fetches vertices from the server and
 * persists them on the server. It does so by issuing the
 * appropriate HTTP requests to the server.
 *
 * @package   ArangoDBClient
 * @since     1.2
 */
class VertexHandler extends DocumentHandler
{
    /**
     * Intermediate function to call the createFromArray function from the right context
     *
     * @param $data
     * @param $options
     *
     * @return Document
     * @throws \ArangoDBClient\ClientException
     */
    public function createFromArrayWithContext($data, $options)
    {
        $_documentClass = $this->_documentClass;

        return $_documentClass::createFromArray($data, $options);
    }
}

class_alias(VertexHandler::class, '\triagens\ArangoDb\VertexHandler');
