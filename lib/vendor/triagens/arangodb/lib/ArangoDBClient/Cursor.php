<?php

/**
 * ArangoDB PHP client: result set cursor
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Provides access to the results of an AQL query or another statement
 *
 * The cursor might not contain all results in the beginning.<br>
 *
 * If the result set is too big to be transferred in one go, the
 * cursor might issue additional HTTP requests to fetch the
 * remaining results from the server.
 *
 * @package   ArangoDBClient
 * @since     0.2
 */
class Cursor implements \Iterator
{
    /**
     * Import $_documentClass functionality
     */
    use DocumentClassable;

    /**
     * The connection object
     *
     * @var Connection
     */
    private $_connection;
    /**
     * Cursor options
     *
     * @var array
     */
    private $_options;

    /**
     * Result Data
     *
     * @var array
     */
    private $data;

    /**
     * The result set
     *
     * @var array
     */
    private $_result;

    /**
     * "has more" indicator - if true, the server has more results
     *
     * @var bool
     */
    private $_hasMore;

    /**
     * cursor id - might be NULL if cursor does not have an id
     *
     * @var mixed
     */
    private $_id;

    /**
     * current position in result set iteration (zero-based)
     *
     * @var int
     */
    private $_position;

    /**
     * total length of result set (in number of documents)
     *
     * @var int
     */
    private $_length;

    /**
     * full count of the result set (ignoring the outermost LIMIT)
     *
     * @var int
     */
    private $_fullCount;

    /**
     * extra data (statistics) returned from the statement
     *
     * @var array
     */
    private $_extra;

    /**
     * number of HTTP calls that were made to build the cursor result
     */
    private $_fetches = 1;

    /**
     * whether or not the query result was served from the AQL query result cache
     */
    private $_cached;
    
    /**
     * precalculated number of documents in the cursor, as returned by the server
     *
     * @var int
     */
    private $_count;

    /**
     * result entry for cursor id
     */
    const ENTRY_ID = 'id';

    /**
     * result entry for "hasMore" flag
     */
    const ENTRY_HASMORE = 'hasMore';

    /**
     * result entry for result documents
     */
    const ENTRY_RESULT = 'result';

    /**
     * result entry for extra data
     */
    const ENTRY_EXTRA = 'extra';

    /**
     * result entry for stats
     */
    const ENTRY_STATS = 'stats';

    /**
     * result entry for the full count (ignoring the outermost LIMIT)
     */
    const FULL_COUNT = 'fullCount';

    /**
     * cache option entry
     */
    const ENTRY_CACHE = 'cache';

    /**
     * cached result attribute - whether or not the result was served from the AQL query cache
     */
    const ENTRY_CACHED = 'cached';

    /**
     * sanitize option entry
     */
    const ENTRY_SANITIZE = '_sanitize';

    /**
     * "flat" option entry (will treat the results as a simple array, not documents)
     */
    const ENTRY_FLAT = '_flat';

    /**
     * "objectType" option entry.
     */
    const ENTRY_TYPE = 'objectType';

    /**
     * "baseurl" option entry.
     */
    const ENTRY_BASEURL = 'baseurl';

    /**
     * Initialise the cursor with the first results and some metadata
     *
     * @param Connection $connection - connection to be used
     * @param array      $data       - initial result data as returned by the server
     * @param array      $options    - cursor options
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function __construct(Connection $connection, array $data, array $options)
    {
        $this->_connection = $connection;
        $this->data        = $data;
        $this->_id         = null;
        $this->_count      = null;
        $this->_extra      = [];
        $this->_cached     = false;

        if (isset($data[self::ENTRY_ID])) {
            $this->_id = $data[self::ENTRY_ID];
        }
        
        if (isset($data[Statement::ENTRY_COUNT])) {
            $this->_count = $data[Statement::ENTRY_COUNT];
        }

        if (isset($data[self::ENTRY_EXTRA])) {
            $this->_extra = $data[self::ENTRY_EXTRA];

            if (isset($this->_extra[self::ENTRY_STATS][self::FULL_COUNT])) {
                $this->_fullCount = $this->_extra[self::ENTRY_STATS][self::FULL_COUNT];
            }
        }

        if (isset($data[self::ENTRY_CACHED])) {
            $this->_cached = $data[self::ENTRY_CACHED];
        }

        // attribute must be there
        assert(isset($data[self::ENTRY_HASMORE]));
        $this->_hasMore = (bool) $data[self::ENTRY_HASMORE];

        if (isset($options['_documentClass'])) {
            $this->setDocumentClass($options['_documentClass']);
        }

        $options['_isNew'] = false;
        $this->_options    = $options;
        $this->_result     = [];
        $this->add((array) $data[self::ENTRY_RESULT]);
        $this->updateLength();

        $this->rewind();
    }


    /**
     * Explicitly delete the cursor
     *
     * This might issue an HTTP DELETE request to inform the server about
     * the deletion.
     *
     * @throws Exception
     * @return bool - true if the server acknowledged the deletion request, false otherwise
     */
    public function delete()
    {
        if ($this->_id) {
            try {
                $this->_connection->delete($this->url() . '/' . $this->_id, []);

                return true;
            } catch (Exception $e) {
            }
        }

        return false;
    }


    /**
     * Get the total number of results in the cursor
     *
     * This might issue additional HTTP requests to fetch any outstanding
     * results from the server
     *
     * @throws Exception
     * @return int - total number of results
     */
    public function getCount()
    {
        if ($this->_count !== null) {
            return $this->_count;
        }

        while ($this->_hasMore) {
            $this->fetchOutstanding();
        }

        return $this->_length;
    }

    /**
     * Get the full count of the cursor (ignoring the outermost LIMIT)
     *
     * @return int - total number of results
     */
    public function getFullCount()
    {
        return $this->_fullCount;
    }


    /**
     * Get the cached attribute for the result set
     *
     * @return bool - whether or not the query result was served from the AQL query cache
     */
    public function getCached()
    {
        return $this->_cached;
    }


    /**
     * Get all results as an array
     *
     * This might issue additional HTTP requests to fetch any outstanding
     * results from the server
     *
     * @throws Exception
     * @return array - an array of all results
     */
    public function getAll()
    {
        while ($this->_hasMore) {
            $this->fetchOutstanding();
        }

        return $this->_result;
    }


    /**
     * Rewind the cursor, necessary for Iterator
     *
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }


    /**
     * Return the current result row, necessary for Iterator
     *
     * @return array - the current result row as an assoc array
     */
    public function current()
    {
        return $this->_result[$this->_position];
    }


    /**
     * Return the index of the current result row, necessary for Iterator
     *
     * @return int - the current result row index
     */
    public function key()
    {
        return $this->_position;
    }


    /**
     * Advance the cursor, necessary for Iterator
     *
     * @return void
     */
    public function next()
    {
        ++$this->_position;
    }


    /**
     * Check if cursor can be advanced further, necessary for Iterator
     *
     * This might issue additional HTTP requests to fetch any outstanding
     * results from the server
     *
     * @throws Exception
     * @return bool - true if the cursor can be advanced further, false if cursor is at end
     */
    public function valid()
    {
        if ($this->_position <= $this->_length - 1) {
            // we have more results than the current position is
            return true;
        }

        if (!$this->_hasMore || !$this->_id) {
            // we don't have more results, but the cursor is exhausted
            return false;
        }

        // need to fetch additional results from the server
        $this->fetchOutstanding();

        return ($this->_position <= $this->_length - 1);
    }


    /**
     * Create an array of results from the input array
     *
     * @param array $data - incoming result
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function add(array $data)
    {
        foreach ($this->sanitize($data) as $row) {
            if (!is_array($row) || (isset($this->_options[self::ENTRY_FLAT]) && $this->_options[self::ENTRY_FLAT])) {
                $this->addFlatFromArray($row);
            } else {
                if (!isset($this->_options['objectType'])) {
                    $this->addDocumentsFromArray($row);
                } else {
                    switch ($this->_options['objectType']) {
                        case 'edge' :
                            $this->addEdgesFromArray($row);
                            break;
                        case 'vertex' :
                            $this->addVerticesFromArray($row);
                            break;
                        case 'path' :
                            $this->addPathsFromArray($row);
                            break;
                        case 'shortestPath' :
                            $this->addShortestPathFromArray($row);
                            break;
                        case 'distanceTo' :
                            $this->addDistanceToFromArray($row);
                            break;
                        case 'commonNeighbors' :
                            $this->addCommonNeighborsFromArray($row);
                            break;
                        case 'commonProperties' :
                            $this->addCommonPropertiesFromArray($row);
                            break;
                        case 'figure' :
                            $this->addFigureFromArray($row);
                            break;
                        default :
                            $this->addDocumentsFromArray($row);
                            break;
                    }
                }
            }
        }
    }


    /**
     * Create an array of results from the input array
     *
     * @param array $data - array of incoming results
     *
     * @return void
     */
    private function addFlatFromArray($data)
    {
        $this->_result[] = $data;
    }


    /**
     * Create an array of documents from the input array
     *
     * @param array $data - array of incoming "document" arrays
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function addDocumentsFromArray(array $data)
    {
        $_documentClass = $this->_documentClass;

        $this->_result[] = $_documentClass::createFromArray($data, $this->_options);
    }

    /**
     * Create an array of paths from the input array
     *
     * @param array $data - array of incoming "paths" arrays
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function addPathsFromArray(array $data)
    {
        $_documentClass = $this->_documentClass;
        $_edgeClass = $this->_edgeClass;

        $entry = [
            'vertices'    => [],
            'edges'       => [],
            'source'      => $_documentClass::createFromArray($data['source'], $this->_options),
            'destination' => $_documentClass::createFromArray($data['destination'], $this->_options),
        ];
        foreach ($data['vertices'] as $v) {
            $entry['vertices'][] = $_documentClass::createFromArray($v, $this->_options);
        }
        foreach ($data['edges'] as $v) {
            $entry['edges'][] = $_edgeClass::createFromArray($v, $this->_options);
        }
        $this->_result[] = $entry;
    }

    /**
     * Create an array of shortest paths from the input array
     *
     * @param array $data - array of incoming "paths" arrays
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function addShortestPathFromArray(array $data)
    {
        $_documentClass = $this->_documentClass;
        $_edgeClass = $this->_edgeClass;

        if (!isset($data['vertices'])) {
            return;
        }

        $vertices    = $data['vertices'];
        $startVertex = $vertices[0];
        $destination = $vertices[count($vertices) - 1];

        $entry = [
            'paths'       => [],
            'source'      => $_documentClass::createFromArray($startVertex, $this->_options),
            'distance'    => $data['distance'],
            'destination' => $_documentClass::createFromArray($destination, $this->_options),
        ];

        $path = [
            'vertices' => [],
            'edges'    => []
        ];

        foreach ($data['vertices'] as $v) {
            $path['vertices'][] = $v;
        }
        foreach ($data['edges'] as $v) {
            $path['edges'][] = $_edgeClass::createFromArray($v, $this->_options);
        }
        $entry['paths'][] = $path;

        $this->_result[] = $entry;
    }


    /**
     * Create an array of distances from the input array
     *
     * @param array $data - array of incoming "paths" arrays
     *
     * @return void
     */
    private function addDistanceToFromArray(array $data)
    {
        $entry           = [
            'source'      => $data['startVertex'],
            'distance'    => $data['distance'],
            'destination' => $data['vertex']
        ];
        $this->_result[] = $entry;
    }

    /**
     * Create an array of common neighbors from the input array
     *
     * @param array $data - array of incoming "paths" arrays
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function addCommonNeighborsFromArray(array $data)
    {
        $_documentClass = $this->_documentClass;

        $left  = $data['left'];
        $right = $data['right'];

        if (!isset($this->_result[$left])) {
            $this->_result[$left] = [];
        }
        if (!isset($this->_result[$left][$right])) {
            $this->_result[$left][$right] = [];
        }

        foreach ($data['neighbors'] as $neighbor) {
            $this->_result[$left][$right][] = $_documentClass::createFromArray($neighbor);
        }
    }

    /**
     * Create an array of common properties from the input array
     *
     * @param array $data - array of incoming "paths" arrays
     *
     * @return void
     */
    private function addCommonPropertiesFromArray(array $data)
    {
        $k                 = array_keys($data);
        $k                 = $k[0];
        $this->_result[$k] = [];
        foreach ($data[$k] as $c) {
            $id = $c['_id'];
            unset($c['_id']);
            $this->_result[$k][$id] = $c;
        }
    }

    /**
     * Create an array of figuresfrom the input array
     *
     * @param array $data - array of incoming "paths" arrays
     *
     * @return void
     */
    private function addFigureFromArray(array $data)
    {
        $this->_result = $data;
    }

    /**
     * Create an array of Edges from the input array
     *
     * @param array $data - array of incoming "edge" arrays
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function addEdgesFromArray(array $data)
    {
        $_edgeClass = $this->_edgeClass;

        $this->_result[] = $_edgeClass::createFromArray($data, $this->_options);
    }


    /**
     * Create an array of Vertex from the input array
     *
     * @param array $data - array of incoming "vertex" arrays
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function addVerticesFromArray(array $data)
    {
        $_documentClass = $this->_documentClass;

        $this->_result[] = $_documentClass::createFromArray($data, $this->_options);
    }


    /**
     * Sanitize the result set rows
     *
     * This will remove the _id and _rev attributes from the results if the
     * "sanitize" option is set
     *
     * @param array $rows - array of rows to be sanitized
     *
     * @return array - sanitized rows
     */
    private function sanitize(array $rows)
    {
        $_documentClass = $this->_documentClass;

        if (isset($this->_options[self::ENTRY_SANITIZE]) && $this->_options[self::ENTRY_SANITIZE]) {
            foreach ($rows as $key => $value) {

                if (is_array($value) && isset($value[$_documentClass::ENTRY_ID])) {
                    unset($rows[$key][$_documentClass::ENTRY_ID]);
                }

                if (is_array($value) && isset($value[$_documentClass::ENTRY_REV])) {
                    unset($rows[$key][$_documentClass::ENTRY_REV]);
                }
            }
        }

        return $rows;
    }


    /**
     * Fetch outstanding results from the server
     *
     * @throws Exception
     * @return void
     */
    private function fetchOutstanding()
    {
        // continuation
        $response = $this->_connection->put($this->url() . '/' . $this->_id, '', []);
        ++$this->_fetches;

        $data = $response->getJson();

        $this->_hasMore = (bool) $data[self::ENTRY_HASMORE];
        $this->add($data[self::ENTRY_RESULT]);

        if (!$this->_hasMore) {
            // we have fetched the complete result set and can unset the id now
            $this->_id = null;
        }

        $this->updateLength();
    }


    /**
     * Set the length of the (fetched) result set
     *
     * @return void
     */
    private function updateLength()
    {
        $this->_length = count($this->_result);
    }


    /**
     * Return the base URL for the cursor
     *
     * @return string
     */
    private function url()
    {
        if (isset($this->_options[self::ENTRY_BASEURL])) {
            return $this->_options[self::ENTRY_BASEURL];
        }

        // this is the fallback
        return Urls::URL_CURSOR;
    }

    /**
     * Get a statistical figure value from the query result
     *
     * @param string $name - name of figure to return
     *
     * @return int
     */
    private function getStatValue($name)
    {
        if (isset($this->_extra[self::ENTRY_STATS][$name])) {
            return $this->_extra[self::ENTRY_STATS][$name];
        }

        return 0;
    }

    /**
     * Get MetaData of the current cursor
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->data;
    }

    /**
     * Return the extra data of the query (statistics etc.). Contents of the result array
     * depend on the type of query executed
     *
     * @return array
     */
    public function getExtra()
    {
        return $this->_extra;
    }

    /**
     * Return the warnings issued during query execution
     *
     * @return array
     */
    public function getWarnings()
    {
        if (isset($this->_extra['warnings'])) {
            return $this->_extra['warnings'];
        }

        return [];
    }

    /**
     * Return the number of writes executed by the query
     *
     * @return int
     */
    public function getWritesExecuted()
    {
        return $this->getStatValue('writesExecuted');
    }

    /**
     * Return the number of ignored write operations from the query
     *
     * @return int
     */
    public function getWritesIgnored()
    {
        return $this->getStatValue('writesIgnored');
    }

    /**
     * Return the number of documents iterated over in full scans
     *
     * @return int
     */
    public function getScannedFull()
    {
        return $this->getStatValue('scannedFull');
    }

    /**
     * Return the number of documents iterated over in index scans
     *
     * @return int
     */
    public function getScannedIndex()
    {
        return $this->getStatValue('scannedIndex');
    }

    /**
     * Return the number of documents filtered by the query
     *
     * @return int
     */
    public function getFiltered()
    {
        return $this->getStatValue('filtered');
    }

    /**
     * Return the number of HTTP calls that were made to build the cursor result
     *
     * @return int
     */
    public function getFetches()
    {
        return $this->_fetches;
    }

    /**
     * Return the cursor id, if any
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }
}

class_alias(Cursor::class, '\triagens\ArangoDb\Cursor');
