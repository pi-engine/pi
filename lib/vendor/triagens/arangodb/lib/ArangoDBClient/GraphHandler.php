<?php

/**
 * ArangoDB PHP client: graph handler
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @author    Frank Mayer
 * @author    Florian Bartels
 * @copyright Copyright 2014, triagens GmbH, Cologne, Germany
 *
 * @since     1.2
 */

namespace ArangoDBClient;

/**
 * A handler that manages graphs.
 *
 * @package ArangoDBClient
 * @since   1.2
 */
class GraphHandler extends Handler
{
    /**
     * documents array index
     */
    const ENTRY_GRAPH = 'graph';

    /**
     * conditional update of edges or vertices
     */
    const OPTION_REVISION = 'revision';

    /**
     * vertex parameter
     */
    const OPTION_VERTICES = 'vertices';

    /**
     * direction parameter
     */
    const OPTION_EDGES = 'edges';

    /**
     * direction parameter
     */
    const OPTION_KEY = '_key';

    /**
     * collection parameter
     */
    const OPTION_COLLECTION = 'collection';

    /**
     * collections parameter
     */
    const OPTION_COLLECTIONS = 'collections';

    /**
     * example parameter
     */
    const KEY_FROM = '_from';

    /**
     * example parameter
     */
    const KEY_TO = '_to';

    /**
     * name parameter
     */
    const OPTION_NAME = 'name';

    /**
     * edge definition parameter
     */
    const OPTION_EDGE_DEFINITION = 'edgeDefinition';

    /**
     * edge definitions parameter
     */
    const OPTION_EDGE_DEFINITIONS = 'edgeDefinitions';

    /**
     * GraphHandler cache store
     */
    protected $cache;

    /**
     * @var $cacheEnabled boolean GraphHandler use cache store
     */
    protected $cacheEnabled = false;

    /**
     * Create a graph
     *
     * This will create a graph using the given graph object and return an array of the created graph object's attributes.<br><br>
     *
     * @throws Exception
     *
     * @param Graph $graph - The graph object which holds the information of the graph to be created
     *
     * @return array
     * @since   1.2
     */
    public function createGraph(Graph $graph)
    {
        $edgeDefinitions = [];
        foreach ($graph->getEdgeDefinitions() as $ed) {
            $edgeDefinitions[] = $ed->transformToArray();
        }

        $params   = [
            self::OPTION_NAME               => $graph->getKey(),
            self::OPTION_EDGE_DEFINITIONS   => $edgeDefinitions,
            self::OPTION_ORPHAN_COLLECTIONS => $graph->getOrphanCollections()
        ];
        $url      = Urls::URL_GRAPH;
        $response = $this->getConnection()->post($url, $this->json_encode_wrapper($params));
        $json     = $response->getJson();
        $graph->setInternalId($json['graph'][Graph::ENTRY_ID]);
        $graph->set(Graph::ENTRY_KEY, $json['graph'][self::OPTION_NAME]);
        $graph->setRevision($json['graph'][Graph::ENTRY_REV]);

        return $graph->getAll();
    }

    /**
     * orphan collection parameter
     */
    const OPTION_ORPHAN_COLLECTIONS = 'orphanCollections';

    /**
     * drop collection
     */
    const OPTION_DROP_COLLECTION = 'dropCollection';

    /**
     * batchsize
     */
    private $batchsize;

    /**
     * count
     */
    private $count;


    /**
     * limit
     */
    private $limit;

    /**
     * Get a graph
     *
     * This will get a graph.<br><br>
     *
     * @param String $graph   - The name of the graph
     * @param array  $options - Options to pass to the method
     *
     * @return Graph|false
     * @throws \ArangoDBClient\ClientException
     * @since   1.2
     */
    public function getGraph($graph, array $options = [])
    {
        $url = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph]);

        try {
            $response = $this->getConnection()->get($url);
        } catch (Exception $e) {
            return false;
        }

        $data = $response->getJson();

        $options['_isNew'] = false;
        
        $result = Graph::createFromArray($data['graph'], $options);
        $result->set(Graph::ENTRY_KEY, $data['graph'][self::OPTION_NAME]);

        return $result;
    }

    /**
     * Sets the batchsize for any method creating a cursor.
     * Will be reset after the cursor has been created.
     *
     * @param int $batchsize
     */
    public function setBatchsize($batchsize)
    {
        $this->batchsize = $batchsize;
    }


    /**
     * Sets the count for any method creating a cursor.
     * Will be reset after the cursor has been created.
     *
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }


    /**
     * Sets the limit for any method creating a cursor.
     * Will be reset after the cursor has been created.
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }


    /**
     * Get a graph's properties<br><br>
     *
     * @throws Exception
     *
     * @param mixed $graph - graph name as a string or instance of Graph
     *
     * @return bool - Returns an array of attributes. Will throw if there is an error
     * @since 1.2
     */
    public function properties($graph)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $url = UrlHelper::buildUrl(Urls::URL_DOCUMENT . '/_graphs', [$graph]);

        $result = $this->getConnection()->get($url);

        return $result->getJson();
    }


    /**
     * Drop a graph and remove all its vertices and edges, also drops vertex and edge collections<br><br>
     *
     * @throws Exception
     *
     * @param mixed $graph           - graph name as a string or instance of Graph
     * @param bool  $dropCollections - if set to false the graphs collections will not be dropped.
     *
     * @return bool - always true, will throw if there is an error
     * @since 1.2
     */
    public function dropGraph($graph, $dropCollections = true)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }


        $url = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph]);
        $url = UrlHelper::appendParamsUrl($url, ['dropCollections' => $dropCollections]);
        $this->getConnection()->delete($url);

        return true;
    }

    /**
     * add an orphan collection to the graph.
     *
     * This will add a further orphan collection to the graph.<br><br>
     *
     *
     * @throws Exception
     *
     * @param mixed  $graph            - graph name as a string or instance of Graph
     * @param string $orphanCollection - the orphan collection to be added as string.
     *
     * @return Graph
     * @since 2.2
     */
    public function addOrphanCollection($graph, $orphanCollection)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $url  = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_VERTEX]);
        $data = [
            self::OPTION_COLLECTION => $orphanCollection
        ];

        try {
            $response = $this->getConnection()->post($url, $this->json_encode_wrapper($data));
        } catch (Exception $e) {
            throw new ClientException($e->getMessage());
        }

        $data = $response->getJson();

        $options['_isNew'] = false;

        $result = Graph::createFromArray($data['graph'], $options);
        $result->set(Graph::ENTRY_KEY, $data['graph'][self::OPTION_NAME]);

        return $result;
    }

    /**
     * deletes an orphan collection from the graph.
     *
     * This will delete an orphan collection from the graph.<br><br>
     *
     *
     * @throws Exception
     *
     * @param mixed   $graph            - graph name as a string or instance of Graph
     * @param string  $orphanCollection - the orphan collection to be removed as string.
     * @param boolean $dropCollection   - if set to true the collection is deleted, not just removed from the graph.
     *
     * @return Graph
     * @since 2.2
     */
    public function deleteOrphanCollection($graph, $orphanCollection, $dropCollection = false)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $url  = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_VERTEX, $orphanCollection]);
        $data = [
            self::OPTION_DROP_COLLECTION => $dropCollection
        ];
        $url  = UrlHelper::appendParamsUrl($url, $data);

        try {
            $response = $this->getConnection()->delete($url);
        } catch (Exception $e) {
            throw new ClientException($e->getMessage());
        }

        $data = $response->getJson();

        $options['_isNew'] = false;

        $result = Graph::createFromArray($data['graph'], $options);
        $result->set(Graph::ENTRY_KEY, $data['graph'][self::OPTION_NAME]);

        return $result;
    }

    /**
     * gets all vertex collection from the graph.
     *
     * This will get all vertex collection (orphans and used in edge definitions) from the graph.<br><br>
     *
     * If this method or any method that calls this method is used in batch mode and caching is off,<br>
     * then for each call, this method will make an out of batch API call to the db in order to get the appropriate collections.<br><br>
     *
     * If caching is on, then the GraphHandler will only call the DB API once for the chosen graph, and return data from cache for the following calls.<br>
     *
     * @param mixed $graph                                                      - graph name as a string or instance of Graph
     * @param array $options                                                    - optional, an array of options
     *                                                                          <p>Options are :<br>
     *                                                                          <li>'excludeOrphans' - boolean value:    true to exclude the orphans or false to include orphans in the result.<br>
     *                                                                          Defaults to false</li>
     *
     * @return array
     * @throws ClientException@since 2.2
     */
    public function getVertexCollections($graph, array $options = [])
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $excludeOrphans = false;
        $_useCache      = $this->cacheEnabled;

        if ((bool) $options && isset($options['excludeOrphans']) && !is_bool($options['excludeOrphans'])) {
            $excludeOrphans = UrlHelper::getBoolString($options['excludeOrphans']);
        }

        if ($_useCache === true) {
            if ($excludeOrphans === true && !empty($this->cache[$graph]['excludeOrphans']['result'])) {
                return $this->cache[$graph]['excludeOrphans']['vertexCollections'];
            }

            if (!empty($this->cache[$graph]['vertexCollections'])) {
                return $this->cache[$graph]['vertexCollections'];
            }
        }
        $url = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_VERTEX]);

        if ($excludeOrphans === true) {
            $url = UrlHelper::appendParamsUrl($url, ['excludeOrphans' => $excludeOrphans]);
        }

        $connection       = $this->getConnection();
        $batchCaptureMode = $connection->isInBatchCaptureMode();

        if ($batchCaptureMode === true) {
            $this->getConnection()->setBatchRequest(false);
        }

        try {
            $response = $this->getConnection()->get($url);
        } catch (Exception $e) {
            throw new ClientException($e->getMessage());
        }

        if ($batchCaptureMode === true) {
            $this->getConnection()->setBatchRequest(true);
        }

        $data = $response->getJson();
        $data = $data[self::OPTION_COLLECTIONS];

        sort($data);

        if ($_useCache === true) {
            if ($excludeOrphans === true && !empty($this->cache[$graph]['excludeOrphans']['vertexCollections'])) {
                $this->cache[$graph]['excludeOrphans']['vertexCollections'] = $data;
            } else {
                $this->cache[$graph]['vertexCollections'] = $data;
            }
        }

        return $data;
    }

    /**
     * adds an edge definition to the graph.
     *
     * This will add a further edge definition to the graph.<br><br>
     *
     *
     * @throws Exception
     *
     * @param mixed          $graph          - graph name as a string or instance of Graph
     * @param EdgeDefinition $edgeDefinition - the new edge definition.
     *
     * @return Graph
     * @since 2.2
     */
    public function addEdgeDefinition($graph, $edgeDefinition)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $url  = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE]);
        $data = $edgeDefinition->transformToArray();

        try {
            $response = $this->getConnection()->post($url, $this->json_encode_wrapper($data));

        } catch (Exception $e) {
            throw new ClientException($e->getMessage());
        }

        $data = $response->getJson();

        $options['_isNew'] = false;

        $result = Graph::createFromArray($data['graph'], $options);
        $result->set(Graph::ENTRY_KEY, $data['graph'][self::OPTION_NAME]);

        return $result;
    }

    /**
     * deletes an edge definition from the graph.
     *
     * This will delete an edge definition from the graph.<br><br>
     *
     *
     * @throws Exception
     *
     * @param mixed   $graph          - graph name as a string or instance of Graph
     * @param string  $edgeDefinition - the name of the edge definitions relation.
     * @param boolean $dropCollection - if set to true the edge definitions collections are deleted.
     *
     * @return Graph
     * @since 2.2
     */
    public function deleteEdgeDefinition($graph, $edgeDefinition, $dropCollection = false)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $url  = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE, $edgeDefinition]);
        $data = [
            self::OPTION_DROP_COLLECTION => $dropCollection
        ];
        $url  = UrlHelper::appendParamsUrl($url, $data);
        try {
            $response = $this->getConnection()->delete($url);
        } catch (Exception $e) {
            throw new ClientException($e->getMessage());
        }

        $data = $response->getJson();

        $options['_isNew'] = false;

        $result = Graph::createFromArray($data['graph'], $options);
        $result->set(Graph::ENTRY_KEY, $data['graph'][self::OPTION_NAME]);

        return $result;
    }

    /**
     * gets all edge collections from the graph.
     *
     * This will get all edge collections from the graph.<br><br>
     *
     * If this method or any method that calls this method is used in batch mode and caching is off,<br>
     * then for each call, this method will make an out of batch API call to the db in order to get the appropriate collections.<br><br>
     *
     * If caching is on, then the GraphHandler will only call the DB API once for the chosen graph, and return data from cache for the following calls.<br>
     *
     * @throws Exception
     *
     * @param mixed $graph - graph name as a string or instance of Graph
     *
     * @return array
     *
     * @since 2.2
     */
    public function getEdgeCollections($graph)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $_useCache = $this->cacheEnabled;

        if ($_useCache === true && !empty($this->cache[$graph]['edgeCollections'])) {
            return $this->cache[$graph]['edgeCollections'];
        }

        $url = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE]);

        $connection       = $this->getConnection();
        $batchCaptureMode = $connection->isInBatchCaptureMode();

        if ($batchCaptureMode === true) {
            $this->getConnection()->setBatchRequest(false);
        }

        try {
            $response = $this->getConnection()->get($url);
        } catch (Exception $e) {
            throw new ClientException($e->getMessage());
        }

        if ($batchCaptureMode === true) {
            $this->getConnection()->setBatchRequest(true);
        }

        $data = $response->getJson();
        $data = $data[self::OPTION_COLLECTIONS];

        sort($data);

        if ($_useCache === true && !empty($this->cache[$graph]['edgeCollections'])) {
            $this->cache[$graph]['edgeCollections'] = $data;
        }

        return $data;
    }


    /**
     * replaces an edge definition of the graph.
     *
     * This will replace an edge definition in the graph.<br><br>
     *
     *
     * @throws Exception
     *
     * @param mixed          $graph          - graph name as a string or instance of Graph
     * @param EdgeDefinition $edgeDefinition - the edge definition.
     *
     * @return Graph
     * @since 2.2
     */
    public function replaceEdgeDefinition($graph, $edgeDefinition)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $url  = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE, $edgeDefinition->getRelation()]);
        $data = $edgeDefinition->transformToArray();

        try {
            $response = $this->getConnection()->put($url, $this->json_encode_wrapper($data));
        } catch (Exception $e) {
            throw new ClientException($e->getMessage());
        }

        $data = $response->getJson();

        $options['_isNew'] = false;

        $result = Graph::createFromArray($data['graph'], $options);
        $result->set(Graph::ENTRY_KEY, $data['graph'][self::OPTION_NAME]);

        return $result;
    }

    /**
     * save a vertex to a graph
     *
     * This will add the vertex-document to the graph and return the vertex id
     *
     * This will throw if the vertex cannot be saved<br><br>
     *
     * @throws Exception
     *
     * @param mixed  $graph      - graph name as a string or instance of Graph
     * @param mixed  $document   - the vertex to be added, can be passed as a vertex object or an array
     * @param string $collection - if one uses a graph with more than one vertex collection one must provide
     *                           the collection to store the vertex.
     *
     * @return string - id of vertex created
     * @since 1.2
     */
    public function saveVertex($graph, $document, $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        if (is_array($document)) {
            $_documentClass = $this->_documentClass;

            $document = $_documentClass::createFromArray($document);
        }

        if ($collection === null) {
            $vertexCollections      = $this->getVertexCollections($graph);
            $vertexCollectionsCount = count($vertexCollections);
            if ($vertexCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($vertexCollectionsCount === 1) {
                $collection = $vertexCollections[0];
            }
        }

        $data = $document->getAll();
        $url  = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_VERTEX, $collection]);

        $response = $this->getConnection()->post($url, $this->json_encode_wrapper($data));

        // This makes sure that if we're in batch mode, it will not go further and choke on the checks below.
        // Caution: Instead of a document ID, we are returning the batchpart object.
        // The Id of the BatchPart can be retrieved by calling getId() on it.
        // We're basically returning an object here, in order not to accidentally use the batch part id as the document id
        if ($batchPart = $response->getBatchPart()) {
            return $batchPart;
        }

        $jsonArray = $response->getJson();
        $vertex    = $jsonArray['vertex'];

        $document->setInternalId($vertex[Vertex::ENTRY_ID]);
        $document->setRevision($vertex[Vertex::ENTRY_REV]);

        $document->setIsNew(false);

        return $document->getInternalId();
    }


    /**
     * Get a single vertex from a graph
     *
     * This will throw if the vertex cannot be fetched from the server<br><br>
     *
     * @throws Exception
     *
     * @param mixed  $graph      - graph name as a string or instance of Graph
     * @param mixed  $vertexId   - the vertex identifier
     * @param array  $options    optional, an array of options:
     *                           <p>
     *                           <li><b>_includeInternals</b> - true to include the internal attributes. Defaults to false</li>
     *                           <li><b>_ignoreHiddenAttributes</b> - true to show hidden attributes. Defaults to false</li>
     *                           </p>
     * @param string $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *                           to load the vertex.
     *
     * @return Document
     * @since 1.2
     */
    public function getVertex($graph, $vertexId, array $options = [], $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }
        $parts = explode('/', $vertexId);
        if (count($parts) === 2) {
            list($collection, $vertexId) = $parts;
        }

        if ($collection === null) {
            $vertexCollections      = $this->getVertexCollections($graph);
            $vertexCollectionsCount = count($vertexCollections);
            if ($vertexCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($vertexCollectionsCount === 1) {
                $collection = $vertexCollections[0];
            }
        }

        $url      = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_VERTEX, $collection, $vertexId]);
        $response = $this->getConnection()->get($url);

        $jsonArray = $response->getJson();
        $vertex    = $jsonArray['vertex'];

        $options['_isNew'] = false;

        $_documentClass = $this->_documentClass;

        return $_documentClass::createFromArray($vertex, $options);
    }


    /**
     * Check if a vertex exists
     *
     * This will call self::getVertex() internally and checks if there
     * was an exception thrown which represents an 404 request.
     *
     * @throws Exception When any other error than a 404 occurs
     *
     * @param mixed $graph    - graph name as a string or instance of Graph
     * @param mixed $vertexId - the vertex identifier
     *
     * @return boolean
     */
    public function hasVertex($graph, $vertexId)
    {
        try {
            // will throw ServerException if entry could not be retrieved
            $this->getVertex($graph, $vertexId);

            return true;
        } catch (ServerException $e) {
            // we are expecting a 404 to return boolean false
            if ($e->getCode() === 404) {
                return false;
            }

            // just rethrow
            throw $e;
        }
    }


    /**
     * Replace an existing vertex in a graph, identified graph name and vertex id
     *
     * This will update the vertex on the server
     *
     * If policy is set to error (locally or globally through the ConnectionOptions)
     * and the passed document has a _rev value set, the database will check
     * that the revision of the to-be-replaced vertex is the same as the one given.<br><br>
     *
     * @throws Exception
     *
     * @param mixed    $graph      - graph name as a string or instance of Graph
     * @param mixed    $vertexId   - the vertex id as string or number
     * @param Document $document   - the vertex-document to be updated
     * @param mixed    $options    optional, an array of options (see below) or the boolean value for $policy (for compatibility prior to version 1.1 of this method):
     *                             <p>
     *                             <li><b>revision</b> - revision for conditional updates ('some-revision-id' [use the passed in revision id], false or true [use document's revision])</li>
     *                             <li><b>policy</b> - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                             <li><b>waitForSync</b> - can be used to force synchronisation of the document replacement operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                             </p>
     * @param string   $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *
     * @return bool - always true, will throw if there is an error
     * @since 1.2
     */
    public function replaceVertex($graph, $vertexId, Document $document, array $options = [], $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        $parts = explode('/', $vertexId);
        if (count($parts) === 2) {
            list($collection, $vertexId) = $parts;
        }

        if ($collection === null) {
            $vertexCollections      = $this->getVertexCollections($graph);
            $vertexCollectionsCount = count($vertexCollections);
            if ($vertexCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($vertexCollectionsCount === 1) {
                $collection = $vertexCollections[0];
            }
        }

        $options = array_merge([self::OPTION_REVISION => false], $options);

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_REPLACE_POLICY)

            ]
        );

        //Include the revision for conditional updates if required
        if ($options[self::OPTION_REVISION] === true) {

            $revision = $document->getRevision();

            if (null !== $revision) {
                $params[ConnectionOptions::OPTION_REVISION] = $revision;
            }
        } elseif ($options[self::OPTION_REVISION]) {
            $params[ConnectionOptions::OPTION_REVISION] = $options[self::OPTION_REVISION];
        }

        $data = $document->getAll();
        $url  = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_VERTEX, $collection, $vertexId]);
        $url  = UrlHelper::appendParamsUrl($url, $params);

        $response = $this->getConnection()->put($url, $this->json_encode_wrapper($data));

        $jsonArray = $response->getJson();
        $vertex    = $jsonArray['vertex'];

        $document->setInternalId($vertex[Vertex::ENTRY_ID]);
        $document->setRevision($vertex[Vertex::ENTRY_REV]);


        return true;
    }


    /**
     * Update an existing vertex in a graph, identified by graph name and vertex id
     *
     * This will update the vertex on the server
     *
     * This will throw if the vertex cannot be updated
     *
     * If policy is set to error (locally or globally through the ConnectionOptions)
     * and the passed vertex-document has a _rev value set, the database will check
     * that the revision of the to-be-replaced document is the same as the one given.<br><br>
     *
     * @throws Exception
     *
     * @param mixed    $graph      - graph name as a string or instance of Graph
     * @param mixed    $vertexId   - the vertex id as string or number
     * @param Document $document   - the patch vertex-document which contains the attributes and values to be updated
     * @param mixed    $options    optional, an array of options (see below)
     *                             <p>
     *                             <li><b>policy</b> - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                             <li><b>keepNull</b> - can be used to instruct ArangoDB to delete existing attributes instead setting their values to null. Defaults to true (keep attributes when set to null)</li>
     *                             <li><b>waitForSync</b> - can be used to force synchronisation of the document update operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                             </p>
     * @param string   $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *
     * @return bool - always true, will throw if there is an error
     * @since 1.2
     */
    public function updateVertex($graph, $vertexId, Document $document, array $options = [], $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }
        $parts = explode('/', $vertexId);
        if (count($parts) === 2) {
            list($collection, $vertexId) = $parts;
        }

        if ($collection === null) {
            $vertexCollections      = $this->getVertexCollections($graph);
            $vertexCollectionsCount = count($vertexCollections);
            if ($vertexCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($vertexCollectionsCount === 1) {
                $collection = $vertexCollections[0];
            }
        }

        $options = array_merge([self::OPTION_REVISION => false], $options);

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'keepNull'    => true,
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_UPDATE_POLICY)

            ]
        );

        //Include the revision for conditional updates if required
        if ($options[self::OPTION_REVISION] === true) {
            $revision = $document->getRevision();

            if (null !== $revision) {
                $params[ConnectionOptions::OPTION_REVISION] = $revision;
            }
        } elseif ($options[self::OPTION_REVISION]) {
            $params[ConnectionOptions::OPTION_REVISION] = $options[self::OPTION_REVISION];
        }

        $url    = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_VERTEX, $collection, $vertexId]);
        $url    = UrlHelper::appendParamsUrl($url, $params);
        $result = $this->getConnection()->patch($url, $this->json_encode_wrapper($document->getAll()));
        $json   = $result->getJson();
        $vertex = $json['vertex'];
        $document->setRevision($vertex[Vertex::ENTRY_REV]);

        return true;
    }


    /**
     * Remove a vertex from a graph, identified by the graph name and vertex id<br><br>
     *
     * @throws Exception
     *
     * @param mixed  $graph      - graph name as a string or instance of Graph
     * @param mixed  $vertexId   - the vertex id as string or number
     * @param mixed  $revision   - optional, the revision of the vertex to be deleted
     * @param mixed  $options    optional, an array of options (see below) or the boolean value for $policy (for compatibility prior to version 1.1 of this method)
     *                           <p>
     *                           <li><b>policy</b> - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                           <li><b>waitForSync</b> - can be used to force synchronisation of the document removal operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                           </p>
     * @param string $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *
     * @return bool - always true, will throw if there is an error
     * @since 1.2
     */
    public function removeVertex($graph, $vertexId, $revision = null, array $options = [], $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }
        $parts = explode('/', $vertexId);
        if (count($parts) === 2) {
            list($collection, $vertexId) = $parts;
        }

        if ($collection === null) {
            $vertexCollections      = $this->getVertexCollections($graph);
            $vertexCollectionsCount = count($vertexCollections);
            if ($vertexCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($vertexCollectionsCount === 1) {
                $collection = $vertexCollections[0];
            }
        }


        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'keepNull'    => true,
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_DELETE_POLICY)

            ]
        );

        if (null !== $revision) {
            $params[ConnectionOptions::OPTION_REVISION] = $revision;
        }

        $url = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_VERTEX, $collection, $vertexId]);
        $url = UrlHelper::appendParamsUrl($url, $params);
        $this->getConnection()->delete($url);

        return true;
    }


    /**
     * save an edge to a graph
     *
     * This will save the edge to the graph and return the edges-document's id
     *
     * This will throw if the edge cannot be saved<br><br>
     *
     * @throws Exception
     *
     * @param mixed  $graph      - graph name as a string or instance of Graph
     * @param mixed  $from       - the 'from' vertex
     * @param mixed  $to         - the 'to' vertex
     * @param mixed  $label      - (optional) a label for the edge
     * @param mixed  $document   - the edge-document to be added, can be passed as an edge object or an array
     * @param string $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *
     * @return mixed - id of edge created
     * @since 1.2
     */
    public function saveEdge($graph, $from, $to, $label = null, $document, $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }

        if ($collection === null) {
            $edgeCollections      = $this->getEdgeCollections($graph);
            $edgeCollectionsCount = count($edgeCollections);
            if ($edgeCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($edgeCollectionsCount === 1) {
                $collection = $edgeCollections[0];
            }
        }

        if (is_array($document)) {
            $_edgeClass = $this->_edgeClass;
            $document = $_edgeClass::createFromArray($document);
        }
        if (null !== $label) {
            $document->set('$label', $label);
        }
        $document->setFrom($from);
        $document->setTo($to);
        $data                 = $document->getAll();
        $data[self::KEY_FROM] = $from;
        $data[self::KEY_TO]   = $to;

        $url = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE, $collection]);

        $response = $this->getConnection()->post($url, $this->json_encode_wrapper($data));

        $jsonArray = $response->getJson();
        $edge      = $jsonArray['edge'];

        $document->setInternalId($edge[Edge::ENTRY_ID]);
        $document->setRevision($edge[Edge::ENTRY_REV]);

        $document->setIsNew(false);

        return $document->getInternalId();
    }


    /**
     * Get a single edge from a graph
     *
     * This will throw if the edge cannot be fetched from the server<br><br>
     *
     * @throws Exception
     *
     * @param mixed  $graph      - graph name as a string or instance of Graph
     * @param mixed  $edgeId     - edge identifier
     * @param array  $options    optional, array of options
     *                           <p>
     *                           <li><b>_includeInternals</b> - true to include the internal attributes. Defaults to false</li>
     *                           <li><b>_ignoreHiddenAttributes</b> - true to show hidden attributes. Defaults to false</li>
     *                           </p>
     * @param string $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *
     * @return Document - the edge document fetched from the server
     * @since 1.2
     */
    public function getEdge($graph, $edgeId, array $options = [], $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }
        $parts = explode('/', $edgeId);
        if (count($parts) === 2) {
            list($collection, $edgeId) = $parts;
        }

        if ($collection === null) {
            $edgeCollections      = $this->getEdgeCollections($graph);
            $edgeCollectionsCount = count($edgeCollections);
            if ($edgeCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($edgeCollectionsCount === 1) {
                $collection = $edgeCollections[0];
            }
        }

        $url      = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE, $collection, $edgeId]);
        $response = $this->getConnection()->get($url);

        $jsonArray = $response->getJson();
        $edge      = $jsonArray['edge'];

        $options['_isNew'] = false;

        $_edgeClass = $this->_edgeClass;
        return $_edgeClass::createFromArray($edge, $options);
    }


    /**
     * Check if an edge exists
     *
     * This will call self::getEdge() internally and checks if there
     * was an exception thrown which represents an 404 request.
     *
     * @throws Exception When any other error than a 404 occurs
     *
     * @param mixed $graph  - graph name as a string or instance of Graph
     * @param mixed $edgeId - the vertex identifier
     *
     * @return boolean
     */
    public function hasEdge($graph, $edgeId)
    {
        try {
            // will throw ServerException if entry could not be retrieved
            $this->getEdge($graph, $edgeId);

            return true;
        } catch (ServerException $e) {
            // we are expecting a 404 to return boolean false
            if ($e->getCode() === 404) {
                return false;
            }

            // just rethrow
            throw $e;
        }
    }


    /**
     * Replace an existing edge in a graph, identified graph name and edge id
     *
     * This will replace the edge on the server
     *
     * This will throw if the edge cannot be Replaced
     *
     * If policy is set to error (locally or globally through the ConnectionOptions)
     * and the passed document has a _rev value set, the database will check
     * that the revision of the to-be-replaced edge is the same as the one given.<br><br>
     *
     * @throws Exception
     *
     * @param mixed  $graph      - graph name as a string or instance of Graph
     * @param mixed  $edgeId     - edge id as string or number
     * @param mixed  $label      - label for the edge or ''
     * @param Edge   $document   - edge document to be updated
     * @param mixed  $options    optional, array of options (see below) or the boolean value for $policy (for compatibility prior to version 1.1 of this method)
     *                           <p>
     *                           <li><b>policy</b> - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                           <li><b>waitForSync</b> - can be used to force synchronisation of the document replacement operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                           </p>
     * @param string $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *
     * @return bool - always true, will throw if there is an error
     * @since 1.2
     */
    public function replaceEdge($graph, $edgeId, $label, Edge $document, array $options = [], $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }
        $parts = explode('/', $edgeId);
        if (count($parts) === 2) {
            list($collection, $edgeId) = $parts;
        }

        if ($collection === null) {
            $edgeCollections      = $this->getEdgeCollections($graph);
            $edgeCollectionsCount = count($edgeCollections);
            if ($edgeCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($edgeCollectionsCount === 1) {
                $collection = $edgeCollections[0];
            }
        }

        $options = array_merge([self::OPTION_REVISION => false], $options);

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'silent'      => false,
                'ignoreRevs'  => true,
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_REPLACE_POLICY),
            ]
        );

        //Include the revision for conditional updates if required
        $headers = [];
        if ($options[self::OPTION_REVISION] === true) {
            $revision = $document->getRevision();

            if (null !== $revision) {
                $params['ignoreRevs'] = false;
                $headers['if-match']  = '"' . $revision . '"';
            }
        } elseif ($options[self::OPTION_REVISION]) {
            $revision             = $options[self::OPTION_REVISION];
            $params['ignoreRevs'] = false;
            $headers['if-match']  = '"' . $revision . '"';
        }

        $data = $document->getAllForInsertUpdate();
        if (null !== $label) {
            $document->set('$label', $label);
        }

        $url = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE, $collection, $edgeId]);
        $url = UrlHelper::appendParamsUrl($url, $params);

        $response = $this->getConnection()->put($url, $this->json_encode_wrapper($data), $headers);

        $jsonArray = $response->getJson();
        $edge      = $jsonArray['edge'];

        $document->setInternalId($edge[Edge::ENTRY_ID]);
        $document->setRevision($edge[Edge::ENTRY_REV]);

        return true;
    }


    /**
     * Update an existing edge in a graph, identified by graph name and edge id
     *
     * This will update the edge on the server
     *
     * This will throw if the edge cannot be updated
     *
     * If policy is set to error (locally or globally through the ConnectionOptions)
     * and the passed edge-document has a _rev value set, the database will check
     * that the revision of the to-be-replaced document is the same as the one given.<br><br>
     *
     * @throws Exception
     *
     * @param mixed  $graph      - graph name as a string or instance of Graph
     * @param mixed  $edgeId     - edge id as string or number
     * @param mixed  $label      - label for the edge or ''
     * @param Edge   $document   - patch edge-document which contains the attributes and values to be updated
     * @param mixed  $options    optional, array of options (see below)
     *                           <p>
     *                           <li><b>policy</b> - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                           <li><b>keepNull</b> - can be used to instruct ArangoDB to delete existing attributes instead setting their values to null. Defaults to true (keep attributes when set to null)</li>
     *                           <li><b>waitForSync</b> - can be used to force synchronisation of the document update operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                           </p>
     * @param string $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *
     * @return bool - always true, will throw if there is an error
     * @since 1.2
     */
    public function updateEdge($graph, $edgeId, $label, Edge $document, array $options = [], $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }
        $parts = explode('/', $edgeId);
        if (count($parts) === 2) {
            list($collection, $edgeId) = $parts;
        }

        if ($collection === null) {
            $edgeCollections      = $this->getEdgeCollections($graph);
            $edgeCollectionsCount = count($edgeCollections);
            if ($edgeCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($edgeCollectionsCount === 1) {
                $collection = $edgeCollections[0];
            }
        }

        $options = array_merge([self::OPTION_REVISION => false], $options);

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'keepNull'    => true,
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_UPDATE_POLICY),
            ]
        );

        //Include the revision for conditional updates if required
        if ($options[self::OPTION_REVISION] === true) {

            $revision = $document->getRevision();

            if (null !== $revision) {
                $params[ConnectionOptions::OPTION_REVISION] = $revision;
            }
        } elseif ($options[self::OPTION_REVISION]) {
            $params[ConnectionOptions::OPTION_REVISION] = $options[self::OPTION_REVISION];
        }

        if (null !== $label) {
            $document->set('$label', $label);
        }

        $url    = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE, $collection, $edgeId]);
        $url    = UrlHelper::appendParamsUrl($url, $params);
        $result = $this->getConnection()->patch($url, $this->json_encode_wrapper($document->getAll()));
        $json   = $result->getJson();
        $edge   = $json['edge'];
        $document->setRevision($edge[Edge::ENTRY_REV]);

        return true;
    }


    /**
     * Remove a edge from a graph, identified by the graph name and edge id<br><br>
     *
     * @throws Exception
     *
     * @param mixed  $graph      - graph name as a string or instance of Graph
     * @param mixed  $edgeId     - edge id as string or number
     * @param mixed  $revision   - optional revision of the edge to be deleted
     * @param mixed  $options    optional, array of options (see below) or the boolean value for $policy (for compatibility prior to version 1.1 of this method)
     *                           <p>
     *                           <li><b>policy</b> - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                           <li><b>waitForSync</b> - can be used to force synchronisation of the document removal operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                           </p>
     * @param string $collection - if one uses a graph with more than one vertex collection one must provide the collection
     *
     * @return bool - always true, will throw if there is an error
     * @since 1.2
     */
    public function removeEdge($graph, $edgeId, $revision = null, array $options = [], $collection = null)
    {
        if ($graph instanceof Graph) {
            $graph = $graph->getKey();
        }
        $parts = explode('/', $edgeId);
        if (count($parts) === 2) {
            list($collection, $edgeId) = $parts;
        }

        if ($collection === null) {
            $edgeCollections      = $this->getEdgeCollections($graph);
            $edgeCollectionsCount = count($edgeCollections);
            if ($edgeCollectionsCount !== 1) {
                throw new ClientException('A collection must be provided.');
            }

            if ($edgeCollectionsCount === 1) {
                $collection = $edgeCollections[0];
            }
        }

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'keepNull'    => true,
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_DELETE_POLICY),
            ]
        );
        if (null !== $revision) {
            $params[ConnectionOptions::OPTION_REVISION] = $revision;
        }

        $url = UrlHelper::buildUrl(Urls::URL_GRAPH, [$graph, Urls::URLPART_EDGE, $collection, $edgeId]);
        $url = UrlHelper::appendParamsUrl($url, $params);
        $this->getConnection()->delete($url);

        return true;
    }

    /**
     * Clears the GraphHandler's cache
     *
     * @return $this
     */
    public function clearCache()
    {
        $this->cache = null;

        return $this;
    }

    /**
     * Checks if caching in enabled
     *
     * @return boolean
     */
    public function getCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    /**
     * @param boolean $useCache
     *
     * @return $this
     */
    public function setCacheEnabled($useCache)
    {
        $this->cacheEnabled = $useCache;

        return $this;
    }
}

class_alias(GraphHandler::class, '\triagens\ArangoDb\GraphHandler');
