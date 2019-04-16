<?php
/**
 * ArangoDB PHP client testsuite
 * File: DocumentExtendedTest.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

/**
 * Class DocumentExtendedTest
 *
 * @property Connection        $connection
 * @property Collection        $collection
 * @property Collection        $edgeCollection
 * @property CollectionHandler $collectionHandler
 * @property DocumentHandler   $documentHandler
 *
 * @package ArangoDBClient
 */
class DocumentExtendedTest extends
    \PHPUnit_Framework_TestCase
{
    protected static $testsTimestamp;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        static::$testsTimestamp = str_replace('.', '_', (string) microtime(true));
    }


    public function setUp()
    {
        $this->connection        = getConnection();
        $this->collectionHandler = new CollectionHandler($this->connection);
        $this->collection        = new Collection();
        $this->collection->setName('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        $this->collectionHandler->create($this->collection);
        $this->documentHandler = new DocumentHandler($this->connection);
    }


    /**
     * test for creation of document with non utf encoding. This tests for failure of such an action.
     * We expect an exception here:
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testCreateDocumentWithWrongEncoding()
    {
        $documentHandler = $this->documentHandler;
        $isoKey          = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'someWrongEncodedAttribute');
        $isoValue        = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'someWrongEncodedValueü');

        $document   = Document::createFromArray([$isoKey => $isoValue, 'someOtherAttribute' => 'someOtherValue']);
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');
        static::assertEquals('someValue', $resultingDocument->someAttribute);
        static::assertEquals('someOtherValue', $resultingDocument->someOtherAttribute);

        $response = $documentHandler->remove($document);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, get, and delete of a document given its settings through createFrom[]
     */
    public function testCreateDocumentWithCreateFromArrayGetAndDeleteDocument()
    {
        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );

        static::assertTrue(isset($document->someAttribute), 'Should return true, as the attribute was set, before.');

        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');
        static::assertEquals('someValue', $resultingDocument->someAttribute);
        static::assertEquals('someOtherValue', $resultingDocument->someOtherAttribute);

        $response = $documentHandler->remove($document);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, get by example, and delete of a document given its settings through createFrom[]
     */
    public function testCreateDocumentWithCreateFromArrayGetByExampleAndDeleteDocument()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $cursor = $this->collectionHandler->byExample($this->collection->getName(), $document);

        static::assertInstanceOf(Cursor::class, $cursor);
        $resultingDocument = $cursor->current();

        static::assertEquals('someValue', $resultingDocument->someAttribute);
        static::assertEquals('someOtherValue', $resultingDocument->someOtherAttribute);

        $response = $documentHandler->remove($document);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, get by example, and delete of a document given its settings through createFrom[]
     */
    public function testCreateDocumentWithCreateFromArrayGetByExampleWithOptionsAndDeleteDocument()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document, ['waitForSync' => true]);

        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute2' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($this->collection->getName(), $document2, ['waitForSync' => true]);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');
        @list(, $documentId2) = explode('/', $documentId2);
        static::assertTrue(is_numeric($documentId2), 'Did not return an id!');

        $exampleDocument = Document::createFromArray(
            ['someAttribute' => 'someValue']
        );

        $cursor = $this->collectionHandler->byExample(
            $this->collection->getName(),
            $exampleDocument,
            ['batchSize' => 1, 'skip' => 0, 'limit' => 2]
        );

        static::assertInstanceOf(Cursor::class, $cursor);
        $resultingDocument = null;
        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertEquals(
            'someValue', $resultingDocument[0]->someAttribute, 'Document returned did not contain expected data.'
        );

        static::assertEquals(
            'someValue', $resultingDocument[1]->someAttribute, 'Document returned did not contain expected data.'
        );

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));

        $cursor = $this->collectionHandler->byExample(
            $this->collection->getName(),
            $exampleDocument,
            ['batchSize' => 1, 'skip' => 1]
        );

        static::assertInstanceOf(Cursor::class, $cursor);
        $resultingDocument = null;
        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertEquals(
            'someValue', $resultingDocument[0]->someAttribute, 'Document returned did not contain expected data.'
        );

        static::assertCount(1, $resultingDocument, 'Should be 1, was: ' . count($resultingDocument));


        $cursor = $this->collectionHandler->byExample(
            $this->collection->getName(),
            $exampleDocument,
            ['batchSize' => 1, 'limit' => 1]
        );

        static::assertInstanceOf(Cursor::class, $cursor);
        $resultingDocument = null;
        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }
        static::assertEquals(
            'someValue', $resultingDocument[0]->someAttribute, 'Document returned did not contain expected data.'
        );
        static::assertCount(1, $resultingDocument, 'Should be 1, was: ' . count($resultingDocument));


        $response = $documentHandler->remove($document);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, get by example, and delete of a document given its settings through createFrom[]
     */
    public function testCreateDocumentWithCreateFromArrayGetFirstExampleAndDeleteDocument()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $resultingDocument = $this->collectionHandler->firstExample($this->collection->getName(), $document);
        static::assertInstanceOf(Document::class, $resultingDocument);

        static::assertEquals('someValue', $resultingDocument->someAttribute);
        static::assertEquals('someOtherValue', $resultingDocument->someOtherAttribute);

        $response = $documentHandler->remove($document);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for updating a document using update()
     */
    public function testUpdateDocument()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);
        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $patchDocument = new Document();
        $patchDocument->set('_id', $document->getHandle());
        $patchDocument->set('_rev', $document->getRevision());
        $patchDocument->set('someOtherAttribute', 'someOtherValue2');
        $result = $documentHandler->update($patchDocument);

        static::assertTrue($result);

        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);
        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');

        static::assertEquals(
            'someValue', $resultingDocument->someAttribute, 'Should be :someValue, is: ' . $resultingDocument->someAttribute
        );
        static::assertEquals(
            'someOtherValue2', $resultingDocument->someOtherAttribute, 'Should be :someOtherValue2, is: ' . $resultingDocument->someOtherAttribute
        );
        $response = $documentHandler->remove($resultingDocument);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for updating a document using update() with wrong encoding
     * We expect an exception here:
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testUpdateDocumentWithWrongEncoding()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);
        $documentHandler->get($this->collection->getName(), $documentId);
        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $patchDocument = new Document();
        $patchDocument->set('_id', $document->getHandle());
        $patchDocument->set('_rev', $document->getRevision());

        // inject wrong encoding
        $isoValue = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'someWrongEncodedValueü');

        $patchDocument->set('someOtherAttribute', $isoValue);
        $result = $documentHandler->update($patchDocument);

        static::assertTrue($result);

        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);
        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');

        static::assertEquals(
            'someValue', $resultingDocument->someAttribute, 'Should be :someValue, is: ' . $resultingDocument->someAttribute
        );
        static::assertEquals(
            'someOtherValue2', $resultingDocument->someOtherAttribute, 'Should be :someOtherValue2, is: ' . $resultingDocument->someOtherAttribute
        );
        $response = $documentHandler->remove($resultingDocument);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for updating a document using update()
     */
    public function testUpdateDocumentDoNotKeepNull()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);
        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $patchDocument = new Document();
        $patchDocument->set('_id', $document->getHandle());
        $patchDocument->set('_rev', $document->getRevision());
        $patchDocument->set('someAttribute', null);
        $patchDocument->set('someOtherAttribute', 'someOtherValue2');
        $result = $documentHandler->update($patchDocument, ['keepNull' => false]);

        static::assertTrue($result);

        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);
        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');

        static::assertEquals(
            null, $resultingDocument->someAttribute, 'Should be : null, is: ' . $resultingDocument->someAttribute
        );
        static::assertEquals(
            'someOtherValue2', $resultingDocument->someOtherAttribute, 'Should be :someOtherValue2, is: ' . $resultingDocument->someOtherAttribute
        );
        $response = $documentHandler->remove($resultingDocument);
        static::assertTrue($response, 'Delete should return true!');
    }
    
    
    /**
     * test for updating a document using returnOld/returnNew
     */
    public function testUpdateDocumentReturnOldNew()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['_key' => 'test', 'value' => 1]
        );
        $documentHandler->insert($this->collection->getName(), $document);

        $patchDocument = new Document();
        $patchDocument->set('_id', $document->getHandle());
        $patchDocument->set('value', 2);
        $result = $documentHandler->update($patchDocument, ['returnOld' => true, 'returnNew' => true]);

        static::assertEquals('test', $result['_key']);
        static::assertEquals('test', $result['old']['_key']);
        static::assertEquals(1, $result['old']['value']);
        static::assertEquals('test', $result['new']['_key']);
        static::assertEquals(2, $result['new']['value']);
        static::assertNotEquals($result['old']['_rev'], $result['new']['_rev']);
    }


    /**
     * test for replacing a document using replace()
     */
    public function testReplaceDocument()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $document->set('someAttribute', 'someValue2');
        $document->set('someOtherAttribute', 'someOtherValue2');
        $result = $documentHandler->replace($document);

        static::assertTrue($result);
        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');

        static::assertEquals(
            'someValue2', $resultingDocument->someAttribute, 'Should be :someValue2, is: ' . $resultingDocument->someAttribute
        );
        static::assertEquals(
            'someOtherValue2', $resultingDocument->someOtherAttribute, 'Should be :someOtherValue2, is: ' . $resultingDocument->someOtherAttribute
        );

        $response = $documentHandler->remove($resultingDocument);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for replacing a document using replace() with wrong encoding
     * We expect an exception here:
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testReplaceDocumentWithWrongEncoding()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        // inject wrong encoding
        $isoKey   = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'someWrongEncododedAttribute');
        $isoValue = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'someWrongEncodedValueü');

        $document->set($isoKey, $isoValue);
        $document->set('someOtherAttribute', 'someOtherValue2');
        $result = $documentHandler->replace($document);

        static::assertTrue($result);
        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');

        static::assertEquals(
            'someValue2', $resultingDocument->someAttribute, 'Should be :someValue2, is: ' . $resultingDocument->someAttribute
        );
        static::assertEquals(
            'someOtherValue2', $resultingDocument->someOtherAttribute, 'Should be :someOtherValue2, is: ' . $resultingDocument->someOtherAttribute
        );

        $response = $documentHandler->remove($resultingDocument);
        static::assertTrue($response, 'Delete should return true!');
    }

    /**
     * test for replacing a document using returnOld/returnNew
     */
    public function testReplaceDocumentReturnOldNew()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['_key' => 'test', 'value' => 1]
        );
        $documentHandler->insert($this->collection->getName(), $document);

        $patchDocument = new Document();
        $patchDocument->set('_id', $document->getHandle());
        $patchDocument->set('value', 2);
        $result = $documentHandler->replace($patchDocument, ['returnOld' => true, 'returnNew' => true]);

        static::assertEquals('test', $result['_key']);
        static::assertEquals('test', $result['old']['_key']);
        static::assertEquals(1, $result['old']['value']);
        static::assertEquals('test', $result['new']['_key']);
        static::assertEquals(2, $result['new']['value']);
        static::assertNotEquals($result['old']['_rev'], $result['new']['_rev']);
    }

    /**
     * test for deletion of a document with deleteById() not giving the revision
     */
    public function testDeleteDocumentWithDeleteByIdWithoutRevision()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $document->set('someAttribute', 'someValue2');
        $document->set('someOtherAttribute', 'someOtherValue2');
        $result = $documentHandler->replace($document);

        static::assertTrue($result);
        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');

        static::assertEquals('someValue2', $resultingDocument->someAttribute);
        static::assertEquals('someOtherValue2', $resultingDocument->someOtherAttribute);

        $response = $documentHandler->removeById($this->collection->getName(), $documentId);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for deletion of a document with deleteById() given the revision
     */
    public function testDeleteDocumentWithDeleteByIdWithRevisionAndPolicyIsError()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $revision = $document->getRevision();
        try {
            $documentHandler->removeById($this->collection->getName(), $documentId, '_UOarUR----', ['policy' => 'error']);
        } catch (ServerException $e) {
            static::assertTrue(true);
        }

        $response = $documentHandler->removeById($this->collection->getName(), $documentId, $revision, ['policy' => 'error']);
        static::assertTrue($response, 'deleteById() should return true! (because correct revision given)');
    }


    /**
     * test for deletion of a document with deleteById() given the revision
     */
    public function testDeleteDocumentWithDeleteByIdWithRevisionAndPolicyIsLast()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $revision = $document->getRevision();

        $response = $documentHandler->removeById($this->collection->getName(), $documentId, '_UOarUR----', ['policy' => 'last']);
        static::assertTrue(
            $response,
            'deleteById() should return true! (because policy  is "last write wins")'
        );
    }
    
    /**
     * test for removing a document using returnOld
     */
    public function testRemoveDocumentReturnOld()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['_key' => 'test', 'value' => 1]
        );
        $documentHandler->insert($this->collection->getName(), $document);

        $patchDocument = new Document();
        $patchDocument->set('_id', $document->getHandle());
        $result = $documentHandler->update($patchDocument, ['returnOld' => true]);

        static::assertEquals('test', $result['_key']);
        static::assertEquals('test', $result['old']['_key']);
        static::assertEquals(1, $result['old']['value']);
    }


    /**
     * test for creation, update, get, and delete having update and delete doing revision checks.
     */
    public function testCreateUpdateGetAndDeleteDocumentWithRevisionCheck()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');


        // Set some new values on the attributes and include the revision in the _rev attribute
        // This should result in a successful update
        $document->set('someAttribute', 'someValue2');
        $document->set('someOtherAttribute', 'someOtherValue2');
        $document->setRevision($resultingDocument->getRevision());

        $result = $documentHandler->update($document, ['policy' => 'error']);

        static::assertTrue($result);
        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals('someValue2', $resultingDocument->someAttribute);
        static::assertEquals('someOtherValue2', $resultingDocument->someOtherAttribute);

        // Set some new values on the attributes and include a fake revision in the _rev attribute
        // This should result in a failure to update
        $document->set('someOtherAttribute', 'someOtherValue3');
        $document->setRevision('_UOarUR----');
        $e = null;
        try {
            $documentHandler->update($document, ['policy' => 'error']);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        static::assertInstanceOf(\Exception::class, $e);
        static::assertEquals('precondition failed', $e->getMessage());
        $resultingDocument1 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals(
            'someValue2', $resultingDocument1->someAttribute, 'This value should not have changed using UPDATE() - this is the behavior of REPLACE()'
        );
        static::assertEquals('someOtherValue2', $resultingDocument1->someOtherAttribute);
        unset ($e);

        $document = Document::createFromArray(['someOtherAttribute' => 'someOtherValue3']);
        $document->setInternalId($this->collection->getName() . '/' . $documentId);
        // Set some new values on the attributes and  _rev attribute to NULL
        // This should result in a successful update
        try {
            $documentHandler->update($document, ['policy' => 'error']);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        $resultingDocument2 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals('someOtherValue3', $resultingDocument2->someOtherAttribute);

        // Set some new values on the attributes and include the revision in the _rev attribute
        // this is only to update the doc and get a new revision for testing the delete method below
        // This should result in a successful update
        $document->set('someAttribute', 'someValue');
        $document->set('someOtherAttribute', 'someOtherValue2');
        $document->set('_rev', $resultingDocument2->getRevision());

        $result = $documentHandler->update($document, ['policy' => 'error']);

        static::assertTrue($result);
        $resultingDocument3 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals($resultingDocument3->someAttribute, 'someValue');
        static::assertEquals($resultingDocument3->someOtherAttribute, 'someOtherValue2');

        $e = null;
        try {
            $documentHandler->remove($resultingDocument, ['policy' => 'error']);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        static::assertInstanceOf(\Exception::class, $e, 'Delete should have raised an exception here');
        static::assertEquals('precondition failed', $e->getMessage());
        unset ($e);

        $response = $documentHandler->remove($resultingDocument3, ['policy' => 'error']);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, update, get, and delete having update and delete doing revision checks.
     */
    public function testMoreCreateUpdateGetAndDeleteDocumentWithRevisionCheck()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');


        // Set some new values on the attributes and include the revision in the _rev attribute
        // This should result in a successful update
        $document->set('someAttribute', 'someValue2');
        $document->set('someOtherAttribute', null);

        $document->set('_rev', $resultingDocument->getRevision());

        $result = $documentHandler->update($document, ['keepNull' => false]);

        static::assertTrue($result);
        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        $resDoc = $resultingDocument->getAll();
        static::assertArrayHasKey('someAttribute', $resDoc);
        static::assertArrayNotHasKey('someOtherAttribute', $resDoc);

        // Set some new values on the attributes and include a fake revision in the _rev attribute
        // This should result in a failure to update
        $document->set('someAttribute', 'someValue3');
        $document->set('someOtherAttribute', 'someOtherValue3');
        $document->set('_rev', '_UOarUR----');

        $e = null;

        try {
            $documentHandler->update($document, ['policy' => 'error']);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        static::assertInstanceOf(\Exception::class, $e);
        static::assertEquals('precondition failed', $e->getMessage());
        $resultingDocument1 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals($resultingDocument1->someAttribute, 'someValue2');
        unset ($e);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue3']
        );
        $document->setInternalId($this->collection->getName() . '/' . $documentId);
        // Set some new values on the attributes and  _rev attribute to NULL
        // This should result in a successful update
        try {
            $documentHandler->update($document, ['policy' => 'error']);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        $resultingDocument2 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals($resultingDocument2->someAttribute, 'someValue3');
        static::assertEquals('someOtherValue3', $resultingDocument2->someOtherAttribute);

        // Set some new values on the attributes and include the revision in the _rev attribute
        // this is only to update the doc and get a new revision for testing the delete method below
        // This should result in a successful update
        $document->set('someAttribute', 'someValue2');
        $document->set('someOtherAttribute', 'someOtherValue2');
        $document->set('_rev', $resultingDocument2->getRevision());

        $result = $documentHandler->update($document, ['policy' => 'error']);

        static::assertTrue($result);
        $resultingDocument3 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals($resultingDocument3->someAttribute, 'someValue2');
        static::assertEquals($resultingDocument3->someOtherAttribute, 'someOtherValue2');

        $e = null;
        try {
            $documentHandler->remove($resultingDocument, ['policy' => 'error']);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        static::assertInstanceOf(\Exception::class, $e, 'Delete should have raised an exception here');
        static::assertEquals('precondition failed', $e->getMessage());
        unset ($e);

        $response = $documentHandler->remove($resultingDocument3, ['policy' => 'error']);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, nulling an attribute, update using/not using keepnull, get, and delete having update and delete doing revision checks.
     */
    public function testCreateSetNullAttributeUpdateGetAndDeleteDocumentWithRevisionCheck()
    {
        $documentHandler = $this->documentHandler;

        $document   = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId = $documentHandler->save($this->collection->getName(), $document);

        @list(, $documentId) = explode('/', $documentId);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');

        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertObjectHasAttribute('_id', $resultingDocument, '_id field should exist, empty or with an id');


        // Set some new values on the attributes and include the revision in the _rev attribute
        // This should result in a successful update
        $document->set('someAttribute', 'someValue2');
        $document->set('someOtherAttribute', 'someOtherValue2');
        $document->setRevision($resultingDocument->getRevision());

        $result = $documentHandler->update($document);

        static::assertTrue($result);
        $resultingDocument = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals('someValue2', $resultingDocument->someAttribute);
        static::assertEquals('someOtherValue2', $resultingDocument->someOtherAttribute);


        // Set an attribute to null and use the keepNull default, which should be true
        $document->set('someOtherAttribute', null);
        $e = null;
        try {
            $documentHandler->update($document);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        $resultingDocument1 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertArrayHasKey('someOtherAttribute', $resultingDocument1->getAll());


        // Set an attribute to null and use keepNull->true
        $document->set('someOtherAttribute', null);
        $e = null;
        try {
            $documentHandler->update($document, ['keepNull' => true]);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        $resultingDocument1 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertArrayHasKey('someOtherAttribute', $resultingDocument1->getAll());


        /// Set an attribute to null and use keepNull -> false
        $document->set('someOtherAttribute', null);
        //        $document->setRevision($resultingDocument->getRevision() - 1000);
        $e = null;
        try {
            $documentHandler->update($document, ['keepNull' => false]);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        $resultingDocument1 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertArrayNotHasKey('someOtherAttribute', $resultingDocument1->getAll());
        //        $this->assertArrayNotHasKey('someAttribute',$resultingDocument1,print_r($resultingDocument1));

        unset ($e);

        $document = Document::createFromArray(['someOtherAttribute' => 'someOtherValue3']);
        $document->setInternalId($this->collection->getName() . '/' . $documentId);
        // Set some new values on the attributes and  _rev attribute to NULL
        // This should result in a successful update
        try {
            $documentHandler->update($document, ['policy' => 'error']);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        $resultingDocument2 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals('someOtherValue3', $resultingDocument2->someOtherAttribute);

        // Set some new values on the attributes and include the revision in the _rev attribute
        // this is only to update the doc and get a new revision for testing the delete method below
        // This should result in a successful update
        $document->set('someAttribute', 'someValue');
        $document->set('someOtherAttribute', 'someOtherValue2');
        $document->set('_rev', $resultingDocument2->getRevision());

        $result = $documentHandler->update($document, ['policy' => 'error']);

        static::assertTrue($result);
        $resultingDocument3 = $documentHandler->get($this->collection->getName(), $documentId);

        static::assertEquals($resultingDocument3->someAttribute, 'someValue');
        static::assertEquals($resultingDocument3->someOtherAttribute, 'someOtherValue2');

        $e = null;
        try {
            $documentHandler->remove($resultingDocument, ['policy' => 'error']);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        static::assertInstanceOf(\Exception::class, $e, 'Delete should have raised an exception here');
        static::assertEquals('precondition failed', $e->getMessage());
        unset ($e);

        $response = $documentHandler->remove($resultingDocument3, ['policy' => 'error']);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test to set some attributes and get all attributes of the document through getAll()
     * Also testing to optionally get internal attributes _id and _rev
     */
    public function testGetAll()
    {
        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            [
                'someAttribute'      => 'someValue',
                'someOtherAttribute' => 'someOtherValue',
                'someThirdAttribute' => 'someThirdValue'
            ]
        );
        $documentHandler->save($this->collection->getName(), $document);

        // set hidden fields
        $document->setHiddenAttributes(['someThirdAttribute']);

        $result = $document->getAll();

        static::assertEquals($result['someAttribute'], 'someValue');
        static::assertEquals($result['someOtherAttribute'], 'someOtherValue');

        // Check if the hidden field is actually hidden...
        static::assertArrayNotHasKey('someThirdAttribute', $result);

        $result = $document->getAll(['_includeInternals' => true]);
        static::assertArrayHasKey('_id', $result);
        static::assertArrayHasKey('_rev', $result);
    }

    /**
     * test to set some attributes and get all attributes of the document through getAll()
     * Also testing to optionally get internal attributes _id and _rev
     */
    public function testHiddenAttributesGetAll()
    {
        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            [
                '_key'     => 'test1',
                'isActive' => true,
                'password' => 'secret',
                'name'     => 'foo'
            ]
        );
        $documentHandler->save($this->collection->getName(), $document);

        $document = Document::createFromArray(
            [
                '_key'     => 'test2',
                'isActive' => false,
                'password' => 'secret',
                'name'     => 'bar'
            ]
        );
        $documentHandler->save($this->collection->getName(), $document);


        $document = $documentHandler->getById($this->collection->getName(), 'test1');
        $document->setHiddenAttributes(['password']);
        $result = $document->getAll();

        static::assertTrue($result['isActive']);
        static::assertEquals('foo', $result['name']);
        static::assertArrayNotHasKey('password', $result);

        // test with even more hidden attributes
        $document = $documentHandler->getById($this->collection->getName(), 'test1');
        $document->setHiddenAttributes(['isActive', 'password', 'foobar']);
        $result = $document->getAll();

        static::assertArrayNotHasKey('isActive', $result);
        static::assertEquals('foo', $result['name']);
        static::assertArrayNotHasKey('password', $result);

        // fetch again, without hidden fields now
        $document = $documentHandler->getById($this->collection->getName(), 'test1');
        $result   = $document->getAll();

        static::assertTrue($result['isActive']);
        static::assertEquals('foo', $result['name']);
        static::assertEquals('secret', $result['password']);


        $document = $documentHandler->getById($this->collection->getName(), 'test2');
        $document->setHiddenAttributes(['password']);
        $result = $document->getAll();

        static::assertFalse($result['isActive']);
        static::assertEquals('bar', $result['name']);
        static::assertArrayNotHasKey('password', $result);

        // test with even more hidden attributes
        $document = $documentHandler->getById($this->collection->getName(), 'test2');
        $document->setHiddenAttributes(['isActive', 'password', 'foobar']);
        $result = $document->getAll();

        static::assertArrayNotHasKey('isActive', $result);
        static::assertEquals('bar', $result['name']);
        static::assertArrayNotHasKey('password', $result);

        // fetch again, without hidden fields now
        $document = $documentHandler->getById($this->collection->getName(), 'test2');
        $result   = $document->getAll();

        static::assertFalse($result['isActive']);
        static::assertEquals('bar', $result['name']);
        static::assertEquals('secret', $result['password']);
    }


    /**
     * Test for correct exception codes if nonexistent objects are tried to be gotten, replaced, updated or removed
     */
    public function testGetReplaceUpdateAndRemoveOnNonExistentObjects()
    {
        // Setup objects
        $documentHandler = $this->documentHandler;
        $document        = Document::createFromArray(
            [
                'someAttribute'      => 'someValue',
                'someOtherAttribute' => 'someOtherValue',
                'someThirdAttribute' => 'someThirdValue'
            ]
        );


        // Try to get a non-existent document out of a nonexistent collection
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $documentHandler->get('nonExistentCollection', 'nonexistentId');
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals($e->getCode(), 404, 'Should be 404, instead got: ' . $e->getCode());


        // Try to get a non-existent document out of an existent collection
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $documentHandler->get($this->collection->getName(), 'nonexistentId');
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals($e->getCode(), 404, 'Should be 404, instead got: ' . $e->getCode());


        // Try to update a non-existent document
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $documentHandler->updateById($this->collection->getName(), 'nonexistentId', $document);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals($e->getCode(), 404, 'Should be 404, instead got: ' . $e->getCode());


        // Try to replace a non-existent document
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $documentHandler->replaceById($this->collection->getName(), 'nonexistentId', $document);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals($e->getCode(), 404, 'Should be 404, instead got: ' . $e->getCode());


        // Try to remove a non-existent document
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $documentHandler->removeById($this->collection->getName(), 'nonexistentId');
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals($e->getCode(), 404, 'Should be 404, instead got: ' . $e->getCode());
    }

    /**
     * Test for correct exception codes if nonexistent objects are tried to be gotten, replaced, updated or removed
     */
    public function testStoreNewDocumentThenReplace()
    {
        //Setup
        $document = new Document();
        $document->set('data', 'this is some test data');

        //Check that the document is new
        static::assertTrue($document->getIsNew(), 'Document is not marked as new when it is a new document.');

        $documentHandler = $this->documentHandler;

        //Store the document
        $id = $documentHandler->store($document, $this->collection->getName());

        $rev = $document->getRevision();

        static::assertEquals($id, $document->getId(), 'Returned ID does not match the one in the document');
        static::assertEquals(
            $document->get('data'), 'this is some test data', 'Data has been modified for some reason.'
        );

        //Check that the document is not new
        static::assertNotTrue($document->getIsNew(), 'Document is marked as new when it is not.');

        //Update the document and save again
        $document->set('data', 'this is some different data');
        $document->set('favorite_sport', 'hockey');
        $documentHandler->store($document);

        //Check that the id remains the same
        static::assertEquals($document->getId(), $id, 'ID of updated document does not match the initial ID.');

        //Retrieve a copy of the document from the server
        $document = $documentHandler->get($this->collection->getName(), $id);

        //Assert that it is not new
        static::assertNotTrue($document->getIsNew(), 'Document is marked as new when it is not.');

        //Assert the id is the same
        static::assertEquals($document->getId(), $id, 'ID of retrieved document does not match expected ID');

        //Assert new data has been saved
        static::assertEquals($document->get('favorite_sport'), 'hockey', 'Retrieved data does not match.');

        static::assertNotEquals($rev, $document->getRevision(), 'Revision matches when it is not suppose to.');
    }

    public function tearDown()
    {
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        unset($this->collectionHandler, $this->collection, $this->connection);
    }
}
