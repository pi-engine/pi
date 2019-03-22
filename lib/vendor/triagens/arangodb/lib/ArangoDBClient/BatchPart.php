<?php

/**
 * ArangoDB PHP client: batchpart
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 * @since   1.1
 *
 */

namespace ArangoDBClient;

/**
 * Provides batch part functionality
 *
 * @package   ArangoDBClient
 * @since     1.1
 */


class BatchPart
{
    /**
     * Import $_documentClass functionality
     */
    use DocumentClassable;


    /**
     * An array of BatchPartCursor options
     *
     * @var array $_batchParts
     */
    private $_cursorOptions = [];


    /**
     * An array of BatchPartCursor options
     *
     * @var array $_batchParts
     */
    private $_id;


    /**
     * An array of BatchPartCursor options
     *
     * @var array $_batchParts
     */
    private $_type;


    /**
     * An array of BatchPartCursor options
     *
     * @var array $_batchParts
     */
    private $_request = [];


    /**
     * An array of BatchPartCursor options
     *
     * @var HttpResponse $_response
     */
    private $_response = [];


    /**
     * The batch that this instance is part of
     *
     * @var Batch $_batch
     */
    private $_batch;


    /**
     * Constructor
     *
     * @param Batch $batch    the batch object, that this part belongs to
     * @param mixed $id       The id of the batch part. TMust be unique and wil be passed to the server in the content-id header
     * @param mixed $type     The type of the request. This is to distinguish the different request type in order to return correct results.
     * @param mixed $request  The request string
     * @param mixed $response The response string
     * @param mixed $options  optional, options like sanitize, that can be passed to the request/response handler.
     *
     */

    public function __construct($batch, $id, $type, $request, $response, $options)
    {
        $sanitize = false;
        $options  = array_merge($options, $this->getCursorOptions());

        if (isset($options['_documentClass'])) {
            $this->setDocumentClass($options['_documentClass']);
        }

        extract($options, EXTR_IF_EXISTS);
        $this->setBatch($batch);
        $this->setId($id);
        $this->setType($type);
        $this->setRequest($request);
        $this->setResponse($response);
        $this->_cursorOptions[Cursor::ENTRY_SANITIZE] = $sanitize;
    }


    /**
     * Sets the id for the current batch part.
     *
     * @param Batch $batch
     *
     * @return BatchPart
     */
    public function setBatch($batch)
    {
        $this->_batch = $batch;

        return $this;
    }


    /**
     * Sets the id for the current batch part.
     *
     * @param mixed $id
     *
     * @return BatchPart
     */
    public function setId($id)
    {
        $this->_id = $id;

        return $this;
    }


    /**
     * Gets the id for the current batch part.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }


    /**
     * Sets the type for the current batch part.
     *
     * @param mixed $type
     *
     * @return BatchPart
     */
    public function setType($type)
    {
        $this->_type = $type;

        return $this;
    }


    /**
     * Gets the type for the current batch part.
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->_type;
    }


    /**
     * Sets the request for the current batch part.
     *
     * @param mixed $request
     *
     * @return BatchPart
     */
    public function setRequest($request)
    {
        $this->_request = $request;

        return $this;
    }


    /**
     * Gets the request for the current batch part.
     *
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }


    /**
     * Sets the response for the current batch part.
     *
     * @param mixed $response
     *
     * @return BatchPart
     */
    public function setResponse($response)
    {
        $this->_response = $response;

        return $this;
    }


    /**
     * Gets the response for the current batch part.
     *
     * @return HttpResponse
     */
    public function getResponse()
    {
        return $this->_response;
    }


    /**
     * Gets the HttpCode for the current batch part.
     *
     * @return int
     */
    public function getHttpCode()
    {
        return $this->getResponse()->getHttpCode();
    }


    /**
     * Get the batch part identified by the array key (0...n) or its id (if it was set with nextBatchPartId($id) )
     *
     * @throws ClientException
     * @return mixed $partId
     */
    public function getProcessedResponse()
    {
        $_documentClass = $this->_documentClass;
        $_edgeClass = $this->_edgeClass;

        $response = $this->getResponse();
        switch ($this->_type) {
            case 'first':
                $json             = $response->getJson();
                if (!isset($json['error']) || $json['error'] === false) {
                    $options           = $this->getCursorOptions();
                    $options['_isNew'] = false;
                    $response          = $_documentClass::createFromArray($json['document'], $options);
                } else {
                    $response          = false;
                }
                break;
            case 'getdocument':
                $json              = $response->getJson();
                $options           = $this->getCursorOptions();
                $options['_isNew'] = false;
                $response          = $_documentClass::createFromArray($json, $options);
                break;
            case 'document':
                $json = $response->getJson();
                if (!isset($json['error']) || $json['error'] === false) {
                    $id       = $json[$_documentClass::ENTRY_ID];
                    $response = $id;
                }
                break;
            case 'getedge':
                $json              = $response->getJson();
                $options           = $this->getCursorOptions();
                $options['_isNew'] = false;
                $response          = $_edgeClass::createFromArray($json, $options);
                break;
            case 'edge':
                $json = $response->getJson();
                if (!isset($json['error']) || $json['error'] === false) {
                    $id       = $json[Edge::ENTRY_ID];
                    $response = $id;
                }
                break;
            case 'getedges':
                $json              = $response->getJson();
                $options           = $this->getCursorOptions();
                $options['_isNew'] = false;
                $response          = [];
                foreach ($json[EdgeHandler::ENTRY_EDGES] as $data) {
                    $response[] = $_edgeClass::createFromArray($data, $options);
                }
                break;
            case 'getcollection':
                $json     = $response->getJson();
                $response = Collection::createFromArray($json);
                break;
            case 'collection':
                $json = $response->getJson();
                if (!isset($json['error']) || $json['error'] === false) {
                    $id       = $json[Collection::ENTRY_ID];
                    $response = $id;
                }
                break;
            case 'cursor':
            case 'all':
            case 'by':
                $options          = $this->getCursorOptions();
                $options['isNew'] = false;

                $options  = array_merge(['_documentClass' => $this->_documentClass], $options);
                $response = new Cursor($this->_batch->getConnection(), $response->getJson(), $options);
                break;
            case 'remove':
                $json     = $response->getJson();
                $response = [
                    'removed' => $json['removed'],
                    'ignored' => $json['ignored']
                ];
                break;
                
            default:
                throw new ClientException('Could not determine response data type.');
                break;
        }

        return $response;
    }


    /**
     * Return an array of cursor options
     *
     * @return array - array of options
     */
    private function getCursorOptions()
    {
        return $this->_cursorOptions;
    }

}

class_alias(BatchPart::class, '\triagens\ArangoDb\BatchPart');
