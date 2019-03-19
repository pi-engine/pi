<?php

/**
 * ArangoDB PHP client: document handler
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @author    Frank Mayer
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;


/**
 * A handler that manages documents
 *
 * A document handler that fetches documents from the server and
 * persists them on the server. It does so by issuing the
 * appropriate HTTP requests to the server.<br>
 *
 * <br>
 *
 * @package   ArangoDBClient
 * @since     0.2
 */
class DocumentHandler extends Handler
{
    /**
     * documents array index
     */
    const ENTRY_DOCUMENTS = 'documents';

    /**
     * collection parameter
     */
    const OPTION_COLLECTION = 'collection';

    /**
     * example parameter
     */
    const OPTION_EXAMPLE = 'example';
    
    /**
     * overwrite option 
     */
    const OPTION_OVERWRITE = 'overwrite';
    
    /**
     * option for returning the old document
     */
    const OPTION_RETURN_OLD = 'returnOld';
    
    /**
     * option for returning the new document
     */
    const OPTION_RETURN_NEW = 'returnNew';


    /**
     * Get a single document from a collection
     *
     * Alias method for getById()
     *
     * @throws Exception
     *
     * @param string $collection  - collection id as a string or number
     * @param mixed  $documentId  - document identifier
     * @param array  $options     - optional, array of options
     *                            <p>Options are :
     *                            <li>'_includeInternals' - true to include the internal attributes. Defaults to false</li>
     *                            <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                            <li>'revision' - the documents revision</li>
     *                            <li>'ifMatch' - boolean if given revision should match or not</li>
     *                            </p>
     *
     * @return Document - the document fetched from the server
     */
    public function get($collection, $documentId, array $options = [])
    {
        return $this->getById($collection, $documentId, $options);
    }


    /**
     * Check if a document exists
     *
     * This will call self::get() internally and checks if there
     * was an exception thrown which represents an 404 request.
     *
     * @throws Exception When any other error than a 404 occurs
     *
     * @param string $collection - collection id as a string or number
     * @param mixed  $documentId - document identifier
     *
     * @return boolean
     */
    public function has($collection, $documentId)
    {
        try {
            // will throw ServerException if entry could not be retrieved
            $this->get($collection, $documentId);

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
     * Get a single document from a collection
     *
     * This will throw if the document cannot be fetched from the server.
     *
     * @throws Exception
     *
     * @param string $collection  - collection id as a string or number
     * @param mixed  $documentId  - document identifier
     * @param array  $options     - optional, array of options
     *                            <p>Options are :
     *                            <li>'_includeInternals' - true to include the internal attributes. Defaults to false</li>
     *                            <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                            <li>'ifMatch' - boolean if given revision should match or not</li>
     *                            <li>'revision' - The document is returned if it matches/not matches revision.</li>
     *                            </p>
     *
     * @return Document - the document fetched from the server
     */
    public function getById($collection, $documentId, array $options = [])
    {
        if (strpos($documentId, '/') !== false) {
            @list($collection ,$documentId) = explode('/', $documentId);
        }
        $data              = $this->getDocument(Urls::URL_DOCUMENT, $collection, $documentId, $options);
        $options['_isNew'] = false;

        return $this->createFromArrayWithContext($data, $options);
    }


    /**
     * Get a single document (internal method)
     *
     * This method is the workhorse for getById() in this handler and the edges handler
     *
     * @throws Exception
     *
     * @param string $url         - the server-side URL being called
     * @param string $collection  - collection id as a string or number
     * @param mixed  $documentId  - document identifier
     * @param array  $options     - optional, array of options
     *                            <p>Options are :
     *                            <li>'_includeInternals' - true to include the internal attributes. Defaults to false</li>
     *                            <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                            <li>'ifMatch' - boolean if given revision should match or not</li>
     *                            <li>'revision' - The document is returned if it matches/not matches revision.</li>
     *                            </p>
     *
     * @internal
     *
     * @return array - the document fetched from the server
     */
    protected function getDocument($url, $collection, $documentId, array $options = [])
    {
        $collection = $this->makeCollection($collection);

        $url            = UrlHelper::buildUrl($url, [$collection, $documentId]);
        $headerElements = [];
        if (array_key_exists('ifMatch', $options) && array_key_exists('revision', $options)) {
            if ($options['ifMatch'] === true) {
                $headerElements['If-Match'] = '"' . $options['revision'] . '"';
            } else {
                $headerElements['If-None-Match'] = '"' . $options['revision'] . '"';
            }
        }

        $response = $this->getConnection()->get($url, $headerElements);

        if ($response->getHttpCode() === 304) {
            throw new ClientException('Document has not changed.');
        }

        return $response->getJson();
    }


    /**
     * Gets information about a single documents from a collection
     *
     * This will throw if the document cannot be fetched from the server
     *
     *
     * @throws Exception
     *
     * @param string  $collection - collection id as a string or number.
     * @param mixed   $documentId - document identifier.
     * @param boolean $ifMatch    -  boolean if given revision should match or not.
     * @param string  $revision   - The document is returned if it matches/not matches revision.
     *
     * @return array - an array containing the complete header including the key httpCode.
     */
    public function getHead($collection, $documentId, $revision = null, $ifMatch = null)
    {
        return $this->head(Urls::URL_DOCUMENT, $collection, $documentId, $revision, $ifMatch);
    }


    /**
     * Get meta-data for a single document (internal method)
     *
     * This method is the workhorse for getHead() in this handler and the edges handler
     *
     * @throws Exception
     *
     * @param string  $url        - the server-side URL being called
     * @param string  $collection - collection id as a string or number
     * @param mixed   $documentId - document identifier
     * @param mixed   $revision   - optional document revision
     * @param boolean $ifMatch    -  boolean if given revision should match or not.
     *
     * @internal
     *
     * @return array - the document meta-data
     */
    protected function head($url, $collection, $documentId, $revision = null, $ifMatch = null)
    {
        $collection = $this->makeCollection($collection);

        $url            = UrlHelper::buildUrl($url, [$collection, $documentId]);
        $headerElements = [];
        if ($revision !== null && $ifMatch !== null) {
            if ($ifMatch) {
                $headerElements['If-Match'] = '"' . $revision . '"';
            } else {
                $headerElements['If-None-Match'] = '"' . $revision . '"';
            }
        }

        $response            = $this->getConnection()->head($url, $headerElements);
        $headers             = $response->getHeaders();
        $headers['httpCode'] = $response->getHttpCode();

        return $headers;
    }


    /**
     * Intermediate function to call the createFromArray function from the right context
     *
     * @param $data
     * @param $options
     *
     * @return Document
     * @throws \ArangoDBClient\ClientException
     */
    protected function createFromArrayWithContext($data, $options)
    {
        $_documentClass = $this->_documentClass;

        return $_documentClass::createFromArray($data, $options);
    }


    /**
     * Store a document to a collection
     *
     * This is an alias/shortcut to save() and replace(). Instead of having to determine which of the 3 functions to use,
     * simply pass the document to store() and it will figure out which one to call.
     *
     * This will throw if the document cannot be saved or replaced.
     *
     * @throws Exception
     *
     * @param Document $document       - the document to be added, can be passed as a document or an array
     * @param mixed    $collection     - collection id as string or number
     * @param array    $options        - optional, array of options
     *                                 <p>Options are :<br>
     *                                 <li>'createCollection' - create the collection if it does not yet exist.</li>
     *                                 <li>'waitForSync' -  if set to true, then all removal operations will instantly be synchronised to disk / If this is not specified, then the collection's default sync behavior will be applied.</li>
     *                                 </p>
     *
     * @return mixed - id of document created
     * @since 1.0
     */
    public function store(Document $document, $collection = null, array $options = [])
    {
        if ($document->getIsNew()) {

            if ($collection === null) {
                throw new ClientException('A collection id is required to store a new document.');
            }

            $result = $this->save($collection, $document, $options);
            $document->setIsNew(false);

            return $result;
        }

        if ($collection) {
            throw new ClientException('An existing document cannot be stored into a new collection');
        }

        return $this->replace($document, $options);
    }


    /**
     * save a document to a collection
     *
     * This will add the document to the collection and return the document's id
     *
     * This will throw if the document cannot be saved
     *
     * @throws Exception
     *
     * @param mixed          $collection - collection id as string or number
     * @param Document|array $document   - the document to be added, can be passed as a document or an array
     * @param array          $options    - optional, array of options
     *                                   <p>Options are :<br>
     *                                   <li>'createCollection' - create the collection if it does not yet exist.</li>
     *                                   <li>'waitForSync' -  if set to true, then all removal operations will instantly be synchronised to disk / If this is not specified, then the collection's default sync behavior will be applied.</li>
     *                                   <li>'overwrite' -  if set to true, will turn the insert into a replace operation if a document with the specified key already exists.</li>
     *                                   <li>'returnNew' -  if set to true, then the newly created document will be returned.</li>
     *                                   <li>'returnOld' -  if set to true, then the replaced document will be returned - useful only when using overwrite = true.</li>
     *                                   </p>
     *
     * @return mixed - id of document created
     * @since 1.0
     */
    public function save($collection, $document, array $options = [])
    {
        $collection     = $this->makeCollection($collection);
        $_documentClass = $this->_documentClass;

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync'      => null,
                'silent'           => false,
                'createCollection' => $this->getConnection()->getOption(ConnectionOptions::OPTION_CREATE),
                'overwrite'        => (bool) @$options[self::OPTION_OVERWRITE],
                'returnOld'        => (bool) @$options[self::OPTION_RETURN_OLD],
                'returnNew'        => (bool) @$options[self::OPTION_RETURN_NEW],
            ]
        );

        $this->createCollectionIfOptions($collection, $params);

        $url = UrlHelper::appendParamsUrl(Urls::URL_DOCUMENT . '/' . $collection, $params);

        if (is_array($document)) {
            $data = $document;
        } else {
            $data = $document->getAllForInsertUpdate();
        }

        $response = $this->getConnection()->post($url, $this->json_encode_wrapper($data));
        $json     = $response->getJson();

        // This makes sure that if we're in batch mode, it will not go further and choke on the checks below.
        // Caution: Instead of a document ID, we are returning the batchpart object
        // The Id of the BatchPart can be retrieved by calling getId() on it.
        // We're basically returning an object here, in order not to accidentally use the batch part id as the document id
        if ($batchPart = $response->getBatchPart()) {
            return $batchPart;
        }
                
        if (@$options[self::OPTION_RETURN_OLD] || @$options[self::OPTION_RETURN_NEW]) {
            return $json;
        }

        if (is_array($document)) {
            return $json[$_documentClass::ENTRY_KEY];
        }

        $location = $response->getLocationHeader();
        if (!$location) {
            throw new ClientException('Did not find location header in server response');
        }

        $id = UrlHelper::getDocumentIdFromLocation($location);

        $document->setInternalId($json[$_documentClass::ENTRY_ID]);
        $document->setRevision($json[$_documentClass::ENTRY_REV]);

        if ($id !== $document->getId()) {
            throw new ClientException('Got an invalid response from the server');
        }

        $document->setIsNew(false);

        return $document->getId();
    }
    
    /**
     * Insert a document into a collection
     * 
     * This is an alias for save().
     */
    public function insert($collection, $document, array $options = []) 
    {
        return $this->save($collection, $document, $options);
    }

    /**
     * Update an existing document in a collection, identified by the including _id and optionally _rev in the patch document.
     * Attention - The behavior of this method has changed since version 1.1
     *
     * This will update the document on the server
     *
     * This will throw if the document cannot be updated
     *
     * If policy is set to error (locally or globally through the ConnectionOptions)
     * and the passed document has a _rev value set, the database will check
     * that the revision of the document to-be-replaced is the same as the one given.
     *
     * @throws Exception
     *
     * @param Document $document - The patch document that will update the document in question
     * @param array    $options  - optional, array of options
     *                           <p>Options are :
     *                           <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                           <li>'keepNull' - can be used to instruct ArangoDB to delete existing attributes instead setting their values to null. Defaults to true (keep attributes when set to null)</li>
     *                           <li>'waitForSync' - can be used to force synchronisation of the document update operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                           </p>
     *
     * @return bool - always true, will throw if there is an error
     */
    public function update(Document $document, array $options = [])
    {
        return $this->updateById($document->getCollectionId(), $this->getDocumentId($document), $document, $options);
    }


    /**
     * Update an existing document in a collection, identified by collection id and document id
     * Attention - The behavior of this method has changed since version 1.1
     *
     * This will update the document on the server
     *
     * This will throw if the document cannot be updated
     *
     * If policy is set to error (locally or globally through the ConnectionOptions)
     * and the passed document has a _rev value set, the database will check
     * that the revision of the document to-be-updated is the same as the one given.
     *
     * @throws Exception
     *
     * @param string   $collection   - collection id as string or number
     * @param mixed    $documentId   - document id as string or number
     * @param Document $document     - patch document which contains the attributes and values to be updated
     * @param array    $options      - optional, array of options
     *                               <p>Options are :
     *                               <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                               <li>'keepNull' - can be used to instruct ArangoDB to delete existing attributes instead setting their values to null. Defaults to true (keep attributes when set to null)</li>
     *                               <li>'waitForSync' - can be used to force synchronisation of the document update operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                               </p>
     *
     * @return bool - always true, will throw if there is an error
     */
    public function updateById($collection, $documentId, Document $document, array $options = [])
    {
        return $this->patch(Urls::URL_DOCUMENT, $collection, $documentId, $document, $options);
    }


    /**
     * Update an existing document in a collection (internal method)
     *
     * @throws Exception
     *
     * @param string   $url          - server-side URL being called
     * @param string   $collection   - collection id as string or number
     * @param mixed    $documentId   - document id as string or number
     * @param Document $document     - patch document which contains the attributes and values to be updated
     * @param array    $options      - optional, array of options
     *                               <p>Options are :
     *                               <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                               <li>'keepNull' - can be used to instruct ArangoDB to delete existing attributes instead setting their values to null. Defaults to true (keep attributes when set to null)</li>
     *                               <li>'waitForSync' - can be used to force synchronisation of the document update operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                               </p>
     *
     * @internal
     *
     * @return bool - always true, will throw if there is an error
     */
    protected function patch($url, $collection, $documentId, Document $document, array $options = [])
    {
        $collection     = $this->makeCollection($collection);
        $_documentClass = $this->_documentClass;

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'keepNull'    => true,
                'silent'      => false,
                'ignoreRevs'  => true,
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_UPDATE_POLICY),
                'returnOld'   => (bool) @$options[self::OPTION_RETURN_OLD],
                'returnNew'   => (bool) @$options[self::OPTION_RETURN_NEW],
            ]
        );


        $headers = [];
        if (isset($params[ConnectionOptions::OPTION_UPDATE_POLICY]) &&
            $params[ConnectionOptions::OPTION_UPDATE_POLICY] === UpdatePolicy::ERROR
        ) {

            $revision = $document->getRevision();
            if (null !== $revision) {
                $params['ignoreRevs'] = false;
                $headers['if-match']  = '"' . $revision . '"';
            }
        }
        
        $url = UrlHelper::buildUrl($url, [$collection, $documentId]);
        $url = UrlHelper::appendParamsUrl($url, $params);
        
        $result = $this->getConnection()->patch($url, $this->json_encode_wrapper($document->getAllForInsertUpdate()), $headers);
        $json   = $result->getJson();
        $document->setRevision($json[$_documentClass::ENTRY_REV]);
        
        if (@$options[self::OPTION_RETURN_OLD] || @$options[self::OPTION_RETURN_NEW]) {
            return $json;
        }

        return true;
    }


    /**
     * Replace an existing document in a collection, identified by the document itself
     *
     * This will update the document on the server
     *
     * This will throw if the document cannot be updated
     *
     * If policy is set to error (locally or globally through the ConnectionOptions)
     * and the passed document has a _rev value set, the database will check
     * that the revision of the to-be-replaced document is the same as the one given.
     *
     * @throws Exception
     *
     * @param Document $document - document to be updated
     * @param array    $options  - optional, array of options
     *                           <p>Options are :
     *                           <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                           <li>'waitForSync' - can be used to force synchronisation of the document update operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                           </p>
     *
     * @return bool - always true, will throw if there is an error
     */
    public function replace(Document $document, array $options = [])
    {
        $documentId = $this->getDocumentId($document);

        return $this->replaceById($document, $documentId, $document, $options);
    }


    /**
     * Replace an existing document in a collection, identified by collection id and document id
     *
     * This will update the document on the server
     *
     * This will throw if the document cannot be Replaced
     *
     * If policy is set to error (locally or globally through the ConnectionOptions)
     * and the passed document has a _rev value set, the database will check
     * that the revision of the to-be-replaced document is the same as the one given.
     *
     * @throws Exception
     *
     * @param mixed    $collection   - collection id as string or number
     * @param mixed    $documentId   - document id as string or number
     * @param Document $document     - document to be updated
     * @param array    $options      - optional, array of options
     *                               <p>Options are :
     *                               <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                               <li>'waitForSync' - can be used to force synchronisation of the document replacement operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                               </p>
     *
     * @return bool - always true, will throw if there is an error
     */
    public function replaceById($collection, $documentId, Document $document, array $options = [])
    {
        return $this->put(Urls::URL_DOCUMENT, $collection, $documentId, $document, $options);
    }


    /**
     * Replace an existing document in a collection (internal method)
     *
     * @throws Exception
     *
     * @param string   $url          - the server-side URL being called
     * @param string   $collection   - collection id as string or number
     * @param mixed    $documentId   - document id as string or number
     * @param Document $document     - document to be updated
     * @param array    $options      - optional, array of options
     *                               <p>Options are :
     *                               <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                               <li>'waitForSync' - can be used to force synchronisation of the document replacement operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                               <li>'ifMatch' - boolean if given revision should match or not</li>
     *                               <li>'revision' - The document is returned if it matches/not matches revision.</li></p>
     *
     * @internal
     *
     * @return bool - always true, will throw if there is an error
     */
    protected function put($url, $collection, $documentId, Document $document, array $options = [])
    {
        $collection     = $this->makeCollection($collection);
        $_documentClass = $this->_documentClass;

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'silent'      => false,
                'ignoreRevs'  => true,
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_REPLACE_POLICY),
                'returnOld'   => (bool) @$options[self::OPTION_RETURN_OLD],
                'returnNew'   => (bool) @$options[self::OPTION_RETURN_NEW],
            ]
        );

        $headers = [];
        if (isset($params[ConnectionOptions::OPTION_REPLACE_POLICY]) &&
            $params[ConnectionOptions::OPTION_REPLACE_POLICY] === UpdatePolicy::ERROR
        ) {
            if (null !== $options['revision']) {
                $params['ignoreRevs'] = false;
                $headers['if-match']  = '"' . $options['revision'] . '"';
            }
        }

        $data = $document->getAllForInsertUpdate();

        $url    = UrlHelper::buildUrl($url, [$collection, $documentId]);
        $url    = UrlHelper::appendParamsUrl($url, $params);
        $result = $this->getConnection()->put($url, $this->json_encode_wrapper($data), $headers);
        $json   = $result->getJson();
        $document->setRevision($json[$_documentClass::ENTRY_REV]);
        
        if (@$options[self::OPTION_RETURN_OLD] || @$options[self::OPTION_RETURN_NEW]) {
            return $json;
        }

        return true;
    }


    /**
     * Remove a document from a collection, identified by the document itself
     *
     * @throws Exception
     *
     * @param Document $document - document to be removed
     * @param array    $options  - optional, array of options
     *                           <p>Options are :
     *                           <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                           <li>'waitForSync' - can be used to force synchronisation of the document removal operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                           </p>
     *
     * @return bool - always true, will throw if there is an error
     */
    public function remove(Document $document, array $options = [])
    {
        $documentId = $this->getDocumentId($document);

        $revision = $this->getRevision($document);

        return $this->removeById($document, $documentId, $revision, $options);
    }


    /**
     * Remove a document from a collection, identified by the collection id and document id
     *
     * @throws Exception
     *
     * @param mixed $collection    - collection id as string or number
     * @param mixed $documentId    - document id as string or number
     * @param mixed $revision      - optional revision of the document to be deleted
     * @param array $options       - optional, array of options
     *                             <p>Options are :
     *                             <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                             <li>'waitForSync' - can be used to force synchronisation of the document removal operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                             </p>
     *
     * @return bool - always true, will throw if there is an error
     */
    public function removeById($collection, $documentId, $revision = null, array $options = [])
    {
        return $this->erase(Urls::URL_DOCUMENT, $collection, $documentId, $revision, $options);
    }


    /**
     * Remove a document from a collection (internal method)
     *
     * @throws Exception
     *
     * @param string $url          - the server-side URL being called
     * @param string $collection   - collection id as string or number
     * @param mixed  $documentId   - document id as string or number
     * @param mixed  $revision     - optional revision of the document to be deleted
     * @param array  $options      - optional, array of options
     *                             <p>Options are :
     *                             <li>'policy' - update policy to be used in case of conflict ('error', 'last' or NULL [use default])</li>
     *                             <li>'waitForSync' - can be used to force synchronisation of the document removal operation to disk even in case that the waitForSync flag had been disabled for the entire collection</li>
     *                             </p>
     *
     * @internal
     *
     * @return bool - always true, will throw if there is an error
     */
    protected function erase($url, $collection, $documentId, $revision = null, array $options = [])
    {
        $collection = $this->makeCollection($collection);

        $params = $this->includeOptionsInParams(
            $options, [
                'waitForSync' => $this->getConnectionOption(ConnectionOptions::OPTION_WAIT_SYNC),
                'silent'      => false,
                'ignoreRevs'  => true,
                'policy'      => $this->getConnectionOption(ConnectionOptions::OPTION_DELETE_POLICY),
                'returnOld'   => (bool) @$options[self::OPTION_RETURN_OLD],
            ]
        );

        $headers = [];
        if (isset($params[ConnectionOptions::OPTION_DELETE_POLICY]) &&
            $params[ConnectionOptions::OPTION_DELETE_POLICY] === UpdatePolicy::ERROR
        ) {

            if (null !== $revision) {
                $params['ignoreRevs'] = false;
                $headers['if-match']  = '"' . $revision . '"';
            }
        }

        $url = UrlHelper::buildUrl($url, [$collection, $documentId]);
        $url = UrlHelper::appendParamsUrl($url, $params);
        
        if (@$options[self::OPTION_RETURN_OLD]) {
            $result = $this->getConnection()->delete($url, $headers);
            $json = $result->getJson();
            return $json;
        }

        $this->getConnection()->delete($url, $headers);
        return true;
    }


    /**
     * Helper function to get a document id from a document or a document id value
     *
     * @throws ClientException
     *
     * @param mixed $document - document id OR document to be updated
     *
     * @return mixed - document id, will throw if there is an error
     */
    private function getDocumentId($document)
    {
        $documentId = $document;
        if ($document instanceof Document) {
            $documentId = $document->getId();
        }

        if (!(is_int($documentId) || is_string($documentId) || is_float($documentId) || trim($documentId) === '')) {
            throw new ClientException('Cannot alter a document without a document id');
        }

        return $documentId;
    }


    /**
     * Helper function to get a document id from a document or a document id value
     *
     * @throws ClientException
     *
     * @param mixed $document - document id OR document to be updated
     *
     * @return mixed - document id, will throw if there is an error
     */
    private function getRevision($document)
    {
        $revision = null;

        if ($document instanceof Document) {
            $revision = $document->getRevision();
        }

        return $revision;
    }

    /**
     * @param       $collection   mixed collection name or id
     * @param array $options      - optional, array of options
     *                            <p>Options are :
     *                            <li>'createCollection' - true to create the collection if it does not exist</li>
     *                            <li>'createCollectionType' - "document" or 2 for document collection</li>
     *                            <li>                         "edge" or 3 for edge collection</li>
     *                            </p>
     */
    protected function createCollectionIfOptions($collection, $options)
    {
        if (!array_key_exists(CollectionHandler::OPTION_CREATE_COLLECTION, $options)) {
            return;
        }

        $value = (bool) $options[CollectionHandler::OPTION_CREATE_COLLECTION];

        if (!$value) {
            return;
        }

        $collectionHandler = new CollectionHandler($this->getConnection());

        if (array_key_exists('createCollectionType', $options)) {
            $options['type'] = $options['createCollectionType'];
            unset($options['createCollectionType']);
        }
        unset($options['createCollection']);
        try {
            // attempt to create the collection
            $collectionHandler->create($collection, $options);
        } catch (Exception $e) {
            // collection may have existed already
        }
    }
}

class_alias(DocumentHandler::class, '\triagens\ArangoDb\DocumentHandler');
