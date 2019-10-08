<?php

/**
 * ArangoDB PHP client: single document
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @author    Florian Bartels
 * @copyright Copyright 2014, triagens GmbH, Cologne, Germany
 *
 * @since     1.2
 */

namespace ArangoDBClient;

/**
 * Value object representing a graph
 *
 * <br>
 *
 * @package   ArangoDBClient
 * @since     1.2
 */
class Graph extends Document
{
    /**
     * Graph edge definitions
     */
    const ENTRY_EDGE_DEFINITIONS = 'edgeDefinitions';

    /**
     * Graph edge definitions from collections
     */
    const ENTRY_FROM = 'from';

    /**
     * Graph edge definitions to collections
     */
    const ENTRY_TO = 'to';

    /**
     * Graph edge definitions collections
     */
    const ENTRY_COLLECTION = 'collection';

    /**
     * Graph orphan collections
     */
    const ENTRY_ORPHAN_COLLECTIONS = 'orphanCollections';

    /**
     * The list of edge definitions defining the graph.
     *
     * @var EdgeDefinition[] list of edge definitions.
     */
    protected $_edgeDefinitions = [];

    /**
     * The list of orphan collections defining the graph.
     * These collections are not used in any edge definition of the graph.
     *
     * @var array list of orphan collections.
     */
    protected $_orphanCollections = [];


    /**
     * Constructs an empty graph
     *
     * @param array $name    - optional, initial name for graph
     * @param array $options - optional, initial options for graph
     *
     * @since     1.2
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function __construct($name = null, array $options = [])
    {

        // prevent backwards compatibility break where the first parameter is the $options array
        if (!is_array($name) && $name !== null) {
            $this->set('_key', $name);
        }

        // pass the $options to the parent constructor to do the actual work
        parent::__construct($options);
    }


    /**
     * Adds an edge definition to the graph.
     *
     * @param EdgeDefinition $edgeDefinition - the edge Definition.
     *
     * @return Graph
     * @since     2.2
     */
    public function addEdgeDefinition(EdgeDefinition $edgeDefinition)
    {
        $this->_edgeDefinitions[] = $edgeDefinition;

        return $this;
    }

    /**
     * Get the edge definitions of the graph.
     *
     * @return EdgeDefinition[]
     * @since     2.2
     */
    public function getEdgeDefinitions()
    {
        return $this->_edgeDefinitions;
    }


    /**
     * Adds an orphan collection to the graph.
     *
     * @param string $orphanCollection - the orphan collection.
     *
     * @return Graph
     * @since     2.2
     */
    public function addOrphanCollection($orphanCollection)
    {
        $this->_orphanCollections[] = $orphanCollection;

        return $this;
    }

    /**
     * Get the orphan collections of the graph.
     *
     * @return string[]
     * @since     2.2
     */
    public function getOrphanCollections()
    {
        return $this->_orphanCollections;
    }


    /**
     * Set a graph attribute
     *
     * The key (attribute name) must be a string.
     * This will validate the value of the attribute and might throw an
     * exception if the value is invalid.
     *
     * @throws ClientException
     *
     * @param string $key   - attribute name
     * @param mixed  $value - value for attribute
     *
     * @return void
     */
    public function set($key, $value)
    {
        if ($key === self::ENTRY_EDGE_DEFINITIONS) {
            if ($this->_doValidate) {
                ValueValidator::validate($value);
            }

            $edgeDefinitionBaseObject = new EdgeDefinition();

            foreach ($value as $ed) {
                $edgeDefinition = clone $edgeDefinitionBaseObject;

                foreach ($ed[self::ENTRY_FROM] as $from) {
                    $edgeDefinition->addFromCollection($from);
                }
                foreach ($ed[self::ENTRY_TO] as $to) {
                    $edgeDefinition->addToCollection($to);
                }
                $edgeDefinition->setRelation($ed[self::ENTRY_COLLECTION]);
                $this->addEdgeDefinition($edgeDefinition);
            }
        } else if ($key === self::ENTRY_ORPHAN_COLLECTIONS) {
            if ($this->_doValidate) {
                ValueValidator::validate($value);
            }

            foreach ($value as $o) {
                $this->addOrphanCollection($o);
            }
        } else {
            parent::set($key, $value);
        }
    }

    /**
     * returns (or creates) the edge definition for single-vertexcollection-undirected graphs, throw an exception for any other type of graph.
     *
     * @throws ClientException
     * @return EdgeDefinition
     */
    private function getSingleUndirectedRelation()
    {
        $ed = $this->getEdgeDefinitions();
        if (count($ed) > 0) {
            $a = $ed[0];
            $b = $a->getFromCollections();
            $c = $a->getToCollections();
        }
        if (count($ed) > 1 ||
            (
                count($ed) === 1 && (
                    count($a->getFromCollections()) > 1 ||
                    count($a->getToCollections()) > 1 ||
                    $b[0] !== $c[0]

                )
            )
        ) {
            throw new ClientException('This operation only supports graphs with one undirected single collection relation');
        }
        if (count($ed) === 1) {
            $eD                     = $ed[0];
            $this->_edgeDefinitions = [];
        } else {
            $eD = new EdgeDefinition();
        }

        return $eD;
    }

}

class_alias(Graph::class, '\triagens\ArangoDb\Graph');
