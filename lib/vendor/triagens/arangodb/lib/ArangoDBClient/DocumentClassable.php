<?php

/**
 * ArangoDB PHP client: mixin for $_documentClass functionality
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2018, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Add functionality for $_documentClass
 *
 * @package ArangoDBClient
 * @since   3.4
 */
trait DocumentClassable
{
    /**
     * @var string Document class to use
     */
    protected $_documentClass = '\ArangoDBClient\Document';
    
    /**
     * @var string Edge class to use
     */
    protected $_edgeClass = '\ArangoDBClient\Edge';

    /**
     * Sets the document class to use
     *
     * @param string $class Document class to use
     * @return DocumentClassable
     */
    public function setDocumentClass($class)
    {
        $this->_documentClass = $class;
        return $this;
    }
    
    /**
     * Sets the edge class to use
     *
     * @param string $class Edge class to use
     * @return DocumentClassable
     */
    public function setEdgeClass($class)
    {
        $this->_edgeClass = $class;
        return $this;
    }

}
