<?php

/**
 * ArangoDB PHP client: result set cursor for exports
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Provides access to the results of a collection export
 *
 * The cursor might not contain all results in the beginning.<br>
 *
 * If the result set is too big to be transferred in one go, the
 * cursor might issue additional HTTP requests to fetch the
 * remaining results from the server.
 *
 * @package   ArangoDBClient
 * @since     2.6
 */
class ExportCursor
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
     * The current result set
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
     * number of HTTP calls that were made to build the cursor result
     */
    private $_fetches = 1;

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
     * "flat" option entry (will treat the results as a simple array, not documents)
     */
    const ENTRY_FLAT = '_flat';

    /**
     * result entry for document count
     */
    const ENTRY_COUNT = 'count';

    /**
     * "type" option entry (is used when converting the result into documents or edges objects)
     */
    const ENTRY_TYPE = 'type';

    /**
     * "baseurl" option entry.
     */
    const ENTRY_BASEURL = 'baseurl';

    /**
     * Initialize the cursor with the first results and some metadata
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

        if (isset($data[self::ENTRY_ID])) {
            $this->_id = $data[self::ENTRY_ID];
        }

        if (isset($options['_documentClass'])) {
            $this->setDocumentClass($options['_documentClass']);
        }

        // attribute must be there
        assert(isset($data[self::ENTRY_HASMORE]));
        $this->_hasMore = (bool) $data[self::ENTRY_HASMORE];

        $this->_options = $options;
        $this->_result  = [];
        $this->setData((array) $data[self::ENTRY_RESULT]);
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
                $this->_connection->delete($this->url() . '/' . $this->_id);

                return true;
            } catch (Exception $e) {
            }
        }

        return false;
    }


    /**
     * Get the total number of results in the export
     *
     * @return int - total number of results
     */
    public function getCount()
    {
        return $this->data[self::ENTRY_COUNT];
    }

    /**
     * Get next results as an array
     *
     * This might issue additional HTTP requests to fetch any outstanding
     * results from the server
     *
     * @throws Exception
     * @return mixed - an array with the next results or false if the cursor is exhausted
     */
    public function getNextBatch()
    {
        if ($this->_result === [] && $this->_hasMore) {
            // read more from server
            $this->fetchOutstanding();
        }

        if ($this->_result !== []) {
            $result        = $this->_result;
            $this->_result = [];

            return $result;
        }

        // cursor is exhausted
        return false;
    }

    /**
     * Create an array of results from the input array
     *
     * @param array $data - incoming result
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function setData(array $data)
    {
        $_documentClass = $this->_documentClass;
        $_edgeClass = $this->_edgeClass;

        if (isset($this->_options[self::ENTRY_FLAT]) && $this->_options[self::ENTRY_FLAT]) {
            $this->_result = $data;
        } else {
            $this->_result = [];

            if ($this->_options[self::ENTRY_TYPE] === Collection::TYPE_EDGE) {
                foreach ($data as $row) {
                    $this->_result[] = $_edgeClass::createFromArray($row, $this->_options);
                }
            } else {
                foreach ($data as $row) {
                    $this->_result[] = $_documentClass::createFromArray($row, $this->_options);
                }
            }
        }
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
        $response = $this->_connection->put($this->url() . '/' . $this->_id, '');
        ++$this->_fetches;

        $data = $response->getJson();

        $this->_hasMore = (bool) $data[self::ENTRY_HASMORE];
        $this->setData($data[self::ENTRY_RESULT]);

        if (!$this->_hasMore) {
            // we have fetched the complete result set and can unset the id now
            $this->_id = null;
        }
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

        // this is the default
        return Urls::URL_EXPORT;
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

class_alias(ExportCursor::class, '\triagens\ArangoDb\ExportCursor');
