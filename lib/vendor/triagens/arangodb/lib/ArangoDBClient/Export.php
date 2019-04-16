<?php

/**
 * ArangoDB PHP client: export
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2015, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Collection export
 *
 * @package ArangoDBClient
 * @since   2.6
 */
class Export
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
     * The collection name or collection object
     *
     * @var mixed
     */
    private $_collection;

    /**
     * The current batch size (number of result documents retrieved per round-trip)
     *
     * @var mixed
     */
    private $_batchSize;

    /**
     * "flat" flag (if set, the query results will be treated as a simple array, not documents)
     *
     * @var bool
     */
    private $_flat = false;

    /**
     * Flush flag (if set, then all documents from the collection that are currently only
     * in the write-ahead log (WAL) will be moved to the collection's datafiles. This may cause
     * an initial delay in the export, but will lead to the documents in the WAL not being
     * excluded from the export run. If the flush flag is set to false, the documents still
     * in the WAL may be missing in the export result.
     *
     * @var bool
     */
    private $_flush = true;

    /**
     * The underlying collection type
     */
    private $_type;

    /**
     * export restrictions - either null for no restrictions or an array with a "type" and a "fields" index
     *
     * @var mixed
     */
    private $_restrictions;

    /**
     * optional limit for export - if specified and positive, will cap the amount of documents in the cursor to
     * the specified value
     *
     * @var int
     */
    private $_limit = 0;

    /**
     * Count option index
     */
    const ENTRY_COUNT = 'count';

    /**
     * Batch size option index
     */
    const ENTRY_BATCHSIZE = 'batchSize';

    /**
     * Flush option index
     */
    const ENTRY_FLUSH = 'flush';

    /**
     * Export restrictions
     */
    const ENTRY_RESTRICT = 'restrict';

    /**
     * Optional limit for the number of documents
     */
    const ENTRY_LIMIT = 'limit';

    /**
     * Initialize the export
     *
     * @throws Exception
     *
     * @param Connection $connection - the connection to be used
     * @param string     $collection - the collection to export
     * @param array      $data       - export options
     */
    public function __construct(Connection $connection, $collection, array $data = [])
    {
        $this->_connection = $connection;

        if (!($collection instanceof Collection)) {
            $collectionHandler = new CollectionHandler($this->_connection);
            $collection        = $collectionHandler->get($collection);
        }
        $this->_collection = $collection;

        // check if we're working with an edge collection or not
        $this->_type = $this->_collection->getType();

        if (isset($data[self::ENTRY_FLUSH])) {
            // set a default value
            $this->_flush = $data[self::ENTRY_FLUSH];
        }

        if (isset($data[self::ENTRY_BATCHSIZE])) {
            $this->setBatchSize($data[self::ENTRY_BATCHSIZE]);
        }

        if (isset($data[self::ENTRY_LIMIT])) {
            $this->_limit = (int) $data[self::ENTRY_LIMIT];
        }

        if (isset($data[self::ENTRY_RESTRICT]) &&
            is_array($data[self::ENTRY_RESTRICT])
        ) {
            $restrictions = $data[self::ENTRY_RESTRICT];

            if (!isset($restrictions['type']) ||
                !in_array($restrictions['type'], ['include', 'exclude'], true)
            ) {
                // validate restrictions.type
                throw new ClientException('Invalid restrictions type definition');
            }

            if (!isset($restrictions['fields']) ||
                !is_array($restrictions['fields'])
            ) {
                // validate restrictions.fields
                throw new ClientException('Invalid restrictions fields definition');
            }

            // all valid 
            $this->_restrictions = $restrictions;
        }

        if (isset($data[ExportCursor::ENTRY_FLAT])) {
            $this->_flat = (bool) $data[ExportCursor::ENTRY_FLAT];
        }
    }

    /**
     * Return the connection object
     *
     * @return Connection - the connection object
     */
    protected function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Execute the export
     *
     * This will return the results as a Cursor. The cursor can then be used to iterate the results.
     *
     * @throws Exception
     * @return ExportCursor
     */
    public function execute()
    {
        $data = [
            self::ENTRY_FLUSH => $this->_flush,
            self::ENTRY_COUNT => true
        ];

        if ($this->_batchSize > 0) {
            $data[self::ENTRY_BATCHSIZE] = $this->_batchSize;
        }

        if ($this->_limit > 0) {
            $data[self::ENTRY_LIMIT] = $this->_limit;
        }

        if (is_array($this->_restrictions)) {
            $data[self::ENTRY_RESTRICT] = $this->_restrictions;
        }

        $collection = $this->_collection;
        if ($collection instanceof Collection) {
            $collection = $collection->getName();
        }

        $url      = UrlHelper::appendParamsUrl(Urls::URL_EXPORT, ['collection' => $collection]);
        $response = $this->_connection->post($url, $this->getConnection()->json_encode_wrapper($data));

        return new ExportCursor($this->_connection, $response->getJson(), $this->getCursorOptions());
    }

    /**
     * Set the batch size for the export
     *
     * The batch size is the number of results to be transferred
     * in one server round-trip. If an export produces more documents
     * than the batch size, it creates a server-side cursor that
     * provides the additional results.
     *
     * The server-side cursor can be accessed by the client with subsequent HTTP requests.
     *
     * @throws ClientException
     *
     * @param int $value - batch size value
     *
     * @return void
     */
    public function setBatchSize($value)
    {
        if (!is_int($value) || (int) $value <= 0) {
            throw new ClientException('Batch size should be a positive integer');
        }

        $this->_batchSize = (int) $value;
    }

    /**
     * Get the batch size for the export
     *
     * @return int - current batch size value
     */
    public function getBatchSize()
    {
        return $this->_batchSize;
    }

    /**
     * Return an array of cursor options
     *
     * @return array - array of options
     */
    private function getCursorOptions()
    {
        $result = [
            ExportCursor::ENTRY_FLAT    => (bool) $this->_flat,
            ExportCursor::ENTRY_BASEURL => Urls::URL_EXPORT,
            ExportCursor::ENTRY_TYPE    => $this->_type,
            '_documentClass'            => $this->_documentClass,
        ];

        return $result;
    }
}

class_alias(Export::class, '\triagens\ArangoDb\Export');
