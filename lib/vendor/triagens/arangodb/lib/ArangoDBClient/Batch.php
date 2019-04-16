<?php

/**
 * ArangoDB PHP client: batch
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 * @since   1.1
 *
 */

namespace ArangoDBClient;

/**
 * Provides batching functionality
 *
 * @package   ArangoDBClient
 * @since     1.1
 */
class Batch
{
    /**
     * Import $_documentClass functionality
     */
    use DocumentClassable;

    /**
     * Batch Response Object
     *
     * @var HttpResponse $_batchResponse
     */
    public $_batchResponse;


    /**
     * Flag that signals if this batch was processed or not. Processed => true ,or not processed => false
     *
     * @var boolean $_processed
     */
    private $_processed = false;


    /**
     * The array of BatchPart objects
     *
     * @var array $_batchParts
     */
    private $_batchParts = [];


    /**
     * The next batch part id
     *
     * @var integer|string $_nextBatchPartId
     */
    private $_nextBatchPartId;


    /**
     * An array of BatchPartCursor options
     *
     * @var array $_batchParts
     */
    private $_batchPartCursorOptions = [];


    /**
     * The connection object
     *
     * @var Connection $_connection
     */
    private $_connection;

    /**
     * The sanitize default value
     *
     * @var bool $_sanitize
     */
    private $_sanitize = false;

    /**
     * The Batch NextId
     *
     * @var integer|string $_nextId
     */
    private $_nextId = 0;


    /**
     * Constructor for Batch instance. Batch instance by default starts capturing request after initiated.
     * To disable this, pass startCapture=>false inside the options array parameter
     *
     * @param Connection $connection that this batch class will monitor for requests in order to batch them. Connection parameter is mandatory.
     * @param array      $options    An array of options for Batch construction. See below for options:
     *
     * <p>Options are :
     * <li>'_sanitize' - True to remove _id and _rev attributes from result documents returned from this batch. Defaults to false.</li>
     * <li>'startCapture' - Start batch capturing immediately after batch instantiation. Defaults to true.</li>
     * <li>'batchSize' - Defines a fixed array size for holding the batch parts. The id's of the batch parts can only be integers.
     *                   When this option is defined, the batch mechanism will use an SplFixedArray instead of the normal PHP arrays.
     *                   In most cases, this will result in increased performance of about 5% to 15%, depending on batch size and data.</li>
     * </p>
     */
    public function __construct(Connection $connection, array $options = [])
    {
        $startCapture = true;
        $sanitize     = false;
        $batchSize    = 0;
        $options      = array_merge($options, $this->getCursorOptions());
        extract($options, EXTR_IF_EXISTS);
        $this->_sanitize = $sanitize;
        $this->batchSize = $batchSize;

        if ($this->batchSize > 0) {
            $this->_batchParts = new \SplFixedArray($this->batchSize);
        }

        $this->setConnection($connection);

        // set default cursor options. Sanitize is currently the only local one.
        $this->_batchPartCursorOptions = [Cursor::ENTRY_SANITIZE => (bool) $this->_sanitize];

        if ($startCapture === true) {
            $this->startCapture();
        }
    }


    /**
     * Sets the connection for he current batch. (mostly internal function)
     *
     * @param Connection $connection
     *
     * @return Batch
     */
    public function setConnection($connection)
    {
        $this->_connection = $connection;

        return $this;
    }


    /**
     * Start capturing requests. To stop capturing, use stopCapture()
     *
     * see ArangoDBClient\Batch::stopCapture()
     *
     * @return Batch
     *
     */
    public function startCapture()
    {
        $this->activate();

        return $this;
    }


    /**
     * Stop capturing requests. If the batch has not been processed yet, more requests can be appended by calling startCapture() again.
     *
     * see Batch::startCapture()
     *
     * @throws ClientException
     * @return Batch
     */
    public function stopCapture()
    {
        // check if this batch is the active one... and capturing. Ignore, if we're not capturing...
        if ($this->isActive() && $this->isCapturing()) {
            $this->setCapture(false);

            return $this;
        }

        throw new ClientException('Cannot stop capturing with this batch. Batch is not active...');
    }


    /**
     * Returns true, if this batch is active in its associated connection.
     *
     * @return bool
     */
    public function isActive()
    {
        $activeBatch = $this->getActive($this->_connection);

        return $activeBatch === $this;
    }


    /**
     * Returns true, if this batch is capturing requests.
     *
     * @return bool
     */
    public function isCapturing()
    {
        return $this->getConnectionCaptureMode($this->_connection);
    }


    /**
     * Activates the batch. This sets the batch active in its associated connection and also starts capturing.
     *
     * @return Batch $this
     */
    public function activate()
    {
        $this->setActive();
        $this->setCapture(true);

        return $this;
    }


    /**
     * Sets the batch active in its associated connection.
     *
     * @return Batch $this
     */
    public function setActive()
    {
        $this->_connection->setActiveBatch($this);

        return $this;
    }


    /**
     * Sets the batch's associated connection into capture mode.
     *
     * @param boolean $state
     *
     * @return Batch $this
     */
    public function setCapture($state)
    {
        $this->_connection->setCaptureBatch($state);

        return $this;
    }


    /**
     * Gets active batch in given connection.
     *
     * @param Connection $connection
     *
     * @return $this
     */
    public function getActive($connection)
    {
        $connection->getActiveBatch();

        return $this;
    }


    /**
     * Returns true, if given connection is in batch-capture mode.
     *
     * @param Connection $connection
     *
     * @return bool
     */
    public function getConnectionCaptureMode($connection)
    {
        return $connection->isInBatchCaptureMode();
    }


    /**
     * Sets connection into Batch-Request mode. This is necessary to distinguish between normal and the batch request.
     *
     * @param boolean $state
     *
     * @return $this
     */
    private function setBatchRequest($state)
    {
        $this->_connection->setBatchRequest($state);
        $this->_processed = true;

        return $this;
    }


    /**
     * Sets the id of the next batch-part. The id can later be used to retrieve the batch-part.
     *
     * @param mixed $batchPartId
     *
     * @return Batch
     */
    public function nextBatchPartId($batchPartId)
    {
        $this->_nextBatchPartId = $batchPartId;

        return $this;
    }


    /**
     * Set client side cursor options (for example: sanitize) for the next batch part.
     *
     * @param mixed $batchPartCursorOptions
     *
     * @return Batch
     */
    public function nextBatchPartCursorOptions($batchPartCursorOptions)
    {
        $this->_batchPartCursorOptions = $batchPartCursorOptions;

        return $this;
    }


    /**
     * Append the request to the batch-part
     *
     * @param mixed $method  - The method of the request (GET, POST...)
     * @param mixed $request - The request that will get appended to the batch
     *
     * @return HttpResponse
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function append($method, $request)
    {
        preg_match('%/_api/simple/(?P<simple>\w*)|/_api/(?P<direct>\w*)%ix', $request, $regs);

        if (!isset($regs['direct'])) {
            $regs['direct'] = '';
        }
        $type = $regs['direct'] !== '' ? $regs['direct'] : $regs['simple'];

        if ($method === 'GET' && $type === $regs['direct']) {
            $type = 'get' . $type;
        }

        if (null === $this->_nextBatchPartId) {
            if (is_a($this->_batchParts, \SplFixedArray::class)) {
                $nextNumeric = $this->_nextId;
                $this->_nextId++;
            } else {
                $nextNumeric = count($this->_batchParts);
            }
            $batchPartId = $nextNumeric;
        } else {
            $batchPartId            = $this->_nextBatchPartId;
            $this->_nextBatchPartId = null;
        }

        $eol = HttpHelper::EOL;

        $result = 'HTTP/1.1 202 Accepted' . $eol;
        $result .= 'location: /_db/_system/_api/document/0/0' . $eol;
        $result .= 'content-type: application/json; charset=utf-8' . $eol;
        $result .= 'etag: "0"' . $eol;
        $result .= 'connection: Close' . $eol . $eol;
        $result .= '{"error":false,"_id":"0/0","id":"0","_rev":0,"hasMore":1, "result":[{}], "documents":[{}]}' . $eol . $eol;

        $response  = new HttpResponse($result);
        $batchPart = new BatchPart($this, $batchPartId, $type, $request, $response, [
            'cursorOptions'  => $this->_batchPartCursorOptions,
            '_documentClass' => $this->_documentClass,
        ]);

        $this->_batchParts[$batchPartId] = $batchPart;

        $response->setBatchPart($batchPart);

        return $response;
    }


    /**
     * Split batch request and use ContentId as array key
     *
     * @param mixed $pattern
     * @param mixed $string
     *
     * @return array $array - Array of batch-parts
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function splitWithContentIdKey($pattern, $string)
    {
        $array    = [];
        $exploded = explode($pattern, $string);
        foreach ($exploded as $key => $value) {
            $response  = new HttpResponse($value);
            $contentId = $response->getHeader('Content-Id');

            if (null !== $contentId) {
                $array[$contentId] = $value;
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }


    /**
     * Processes this batch. This sends the captured requests to the server as one batch.
     *
     * @return HttpResponse|Batch - Batch if processing of the batch was successful or the HttpResponse object in case of a failure. A successful process just means that tha parts were processed. Each part has it's own response though and should be checked on its own.
     *
     * @throws ClientException
     * @throws \ArangoDBClient\Exception
     */
    public function process()
    {
        if ($this->isCapturing()) {
            $this->stopCapture();
        }
        $this->setBatchRequest(true);
        $data       = '';
        $batchParts = $this->getBatchParts();

        if (count($batchParts) === 0) {
            throw new ClientException('Can\'t process empty batch.');
        }

        $combinedDataHeader = '--' . HttpHelper::MIME_BOUNDARY . HttpHelper::EOL;
        $combinedDataHeader .= 'Content-Type: application/x-arango-batchpart' . HttpHelper::EOL;

        /** @var $partValue BatchPart */
        foreach ($batchParts as $partValue) {
            if (null !== $partValue) {

                $data .= $combinedDataHeader;
                if (null !== $partValueId = $partValue->getId()) {
                    $data .= 'Content-Id: ' . (string) $partValueId . HttpHelper::SEPARATOR;
                } else {
                    $data .= HttpHelper::EOL;
                }

                $data .= (string) $partValue->getRequest() . HttpHelper::EOL;
            }
        }

        $data .= '--' . HttpHelper::MIME_BOUNDARY . '--' . HttpHelper::SEPARATOR;

        $params               = [];
        $url                  = UrlHelper::appendParamsUrl(Urls::URL_BATCH, $params);
        $this->_batchResponse = $this->_connection->post($url, $data);

        if ($this->_batchResponse->getHttpCode() !== 200) {
            return $this->_batchResponse;
        }

        $body       = $this->_batchResponse->getBody();
        $body       = trim($body, '--' . HttpHelper::MIME_BOUNDARY . '--');
        $batchParts = $this->splitWithContentIdKey('--' . HttpHelper::MIME_BOUNDARY . HttpHelper::EOL, $body);

        foreach ($batchParts as $partKey => $partValue) {
            $response                     = new HttpResponse($partValue);
            $body                         = $response->getBody();
            $response                     = new HttpResponse($body);
            $batchPartResponses[$partKey] = $response;
            $this->getPart($partKey)->setResponse($batchPartResponses[$partKey]);
        }

        return $this;
    }


    /**
     * Get the total count of the batch parts
     *
     * @return integer $count
     */
    public function countParts()
    {
        return count($this->_batchParts);
    }


    /**
     * Get the batch part identified by the array key (0...n) or its id (if it was set with nextBatchPartId($id) )
     *
     * @param mixed $partId the batch part id. Either it's numeric key or a given name.
     *
     * @return mixed $batchPart
     *
     * @throws ClientException
     */
    public function getPart($partId)
    {
        if (!isset($this->_batchParts[$partId])) {
            throw new ClientException('Request batch part does not exist.');
        }

        return $this->_batchParts[$partId];
    }


    /**
     * Get the batch part identified by the array key (0...n) or its id (if it was set with nextBatchPartId($id) )
     *
     * @param mixed $partId the batch part id. Either it's numeric key or a given name.
     *
     * @return mixed $partId
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function getPartResponse($partId)
    {
        return $this->getPart($partId)->getResponse();
    }


    /**
     * Get the batch part identified by the array key (0...n) or its id (if it was set with nextBatchPartId($id) )
     *
     * @param mixed $partId the batch part id. Either it's numeric key or a given name.
     *
     * @return mixed $partId
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function getProcessedPartResponse($partId)
    {
        return $this->getPart($partId)->getProcessedResponse();
    }


    /**
     * Returns the array of batch-parts
     *
     * @return array $_batchParts
     */
    public function getBatchParts()
    {
        return $this->_batchParts;
    }


    /**
     * Return an array of cursor options
     *
     * @return array - array of options
     */
    private function getCursorOptions()
    {
        return $this->_batchPartCursorOptions;
    }


    /**
     * Return this batch's connection
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

}

class_alias(Batch::class, '\triagens\ArangoDb\Batch');
