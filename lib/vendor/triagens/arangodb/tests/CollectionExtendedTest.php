<?php
/**
 * ArangoDB PHP client testsuite
 * File: CollectionExtendedTest.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

/**
 * Class CollectionExtendedTest
 *
 * @property Connection        $connection
 * @property Collection        $collection
 * @property CollectionHandler $collectionHandler
 * @property DocumentHandler   $documentHandler
 *
 * @package ArangoDBClient
 */
class CollectionExtendedTest extends
    \PHPUnit_Framework_TestCase
{
    protected static $testsTimestamp;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        static::$testsTimestamp = str_replace('.', '_', (string) microtime(true));
    }


    /**
     * Test set-up
     */
    public function setUp()
    {
        $this->connection        = getConnection();
        $this->collection        = new Collection();
        $this->collectionHandler = new CollectionHandler($this->connection);
        $this->documentHandler   = new DocumentHandler($this->connection);

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        $adminHandler = new AdminHandler($this->connection);
        $this->isMMFilesEngine   = ($adminHandler->getEngine()["name"] == "mmfiles"); 
    }


    /**
     * test for creation, get, and delete of a collection with waitForSync default value (no setting)
     */
    public function testCreateGetAndDeleteCollectionWithWaitForSyncDefault()
    {
        $collection        = $this->collection;
        $collectionHandler = $this->collectionHandler;

        $resultingAttribute = $collection->getWaitForSync();
        static::assertNull($resultingAttribute, 'Default waitForSync in collection should be NULL!');

        $name = 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp;
        $collection->setName($name);


        $response = $collectionHandler->create($collection);

        static::assertTrue(is_numeric($response), 'Adding collection did not return an id!');

        $collectionHandler->get($name);

        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, getProperties, and delete of a volatile (in-memory-only) collection
     */
    public function testCreateGetAndDeleteVolatileCollection()
    {
        if (!$this->isMMFilesEngine) {
            $this->markTestSkipped("test is only meaningful with the mmfiles engine");
        }

        $collection        = $this->collection;
        $collectionHandler = $this->collectionHandler;

        $resultingAttribute = $collection->getIsVolatile();
        static::assertNull($resultingAttribute, 'Default waitForSync in API should be NULL!');

        $name = 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp;
        $collection->setName($name);
        $collection->setIsVolatile(true);


        $response = $collectionHandler->create($collection);

        static::assertTrue(is_numeric($response), 'Adding collection did not return an id!');

        $collectionHandler->get($name);

        $properties = $collectionHandler->getProperties($name);
        static::assertTrue((!$this->isMMFilesEngine) || $properties->getIsVolatile(), '"isVolatile" should be true!');


        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, getProperties, and delete of a volatile (in-memory-only) collection
     */
    public function testCreateGetAndDeleteSystemCollection()
    {
        $collection        = $this->collection;
        $collectionHandler = $this->collectionHandler;

        $resultingAttribute = $collection->getIsSystem();
        static::assertNull($resultingAttribute, 'Default isSystem in API should be NULL!');

        $name = '_ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp;
        $collection->setName($name);
        $collection->setIsSystem(true);

        try {
            $collectionHandler->drop($name, ['isSystem' => true]);
        } catch (Exception $e) {
            //Silence the exception
        }

        $response = $collectionHandler->create($collection);

        static::assertTrue(is_numeric($response), 'Adding collection did not return an id!');

        $collectionHandler->get($name);

        $properties = $collectionHandler->getProperties($name);
        static::assertTrue($properties->getIsSystem(), '"isSystem" should be true!');

        $response = $collectionHandler->drop($collection, ['isSystem' => true]);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for getting all collection exclude system collections
     */
    public function testGetAllNonSystemCollections()
    {
        $collectionHandler = $this->collectionHandler;

        $collections = [
            'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp,
            'ArangoDB_PHP_TestSuite_TestCollection_02' . '_' . static::$testsTimestamp
        ];

        foreach ($collections as $col) {
            $collection = new Collection();
            $collection->setName($col);
            $collectionHandler->create($collection);
        }

        $collectionList = $collectionHandler->getAllCollections($options = ['excludeSystem' => true]);

        foreach ($collections as $col) {
            static::assertArrayHasKey($col, $collectionList, 'Collection name should be in collectionList');
        }

        static::assertArrayNotHasKey(
            '_structures',
            $collectionList,
            'System collection _structure should not be returned'
        );

        foreach ($collections as $col) {
            $collectionHandler->drop($col);
        }
    }

    /**
     * test for getting the Checksum for a collection containing 3 documents in different varieties
     */
    public function testGetChecksum()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collectionHandler = $this->collectionHandler;
        $documentHandler   = $this->documentHandler;

        $collection = new Collection();
        $collection->setName('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);

        $collection->setId($collectionHandler->create($collection));

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);
        $document2 = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document2);
        $document3 = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document3);

        $checksum1 = $collectionHandler->getChecksum($collection->getName(), true, true);
        $checksum2 = $collectionHandler->getChecksum($collection->getName());
        $checksum3 = $collectionHandler->getChecksum($collection->getName(), false, true);
        $checksum4 = $collectionHandler->getChecksum($collection->getName(), true);
        $revision  = $checksum1['revision'];
        static::assertEquals($revision, $checksum2['revision']);
        static::assertEquals($revision, $checksum3['revision']);
        static::assertEquals($revision, $checksum4['revision']);

        static::assertNotEquals($checksum1['checksum'], $checksum2['checksum']);
        static::assertNotEquals($checksum1['checksum'], $checksum3['checksum']);
        static::assertNotEquals($checksum1['checksum'], $checksum4['checksum']);
        static::assertNotEquals($checksum2['checksum'], $checksum3['checksum']);
        static::assertNotEquals($checksum2['checksum'], $checksum4['checksum']);
        static::assertNotEquals($checksum3['checksum'], $checksum4['checksum']);

        $collectionHandler->drop($collection);
    }

    /**
     *
     * test for getting the Checksum for a non existing collection
     */
    public function testGetChecksumWithException()
    {
        $collectionHandler = $this->collectionHandler;
        try {
            $collectionHandler->getChecksum('nonExisting', true, true);
        } catch (\Exception $e) {
            static::assertEquals(404, $e->getCode());
        }
    }

    /**
     * test for getting the , true, true for a collection
     */
    public function testGetRevision()
    {
        $collectionHandler = $this->collectionHandler;
        $documentHandler   = $this->documentHandler;

        $collection = new Collection();
        $collection->setName('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);

        $collection->setId($collectionHandler->create($collection));
        $revision = $collectionHandler->getRevision($collection->getName());
        static::assertArrayHasKey('revision', $revision);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $revision2 = $collectionHandler->getRevision($collection->getName());

        static::assertNotEquals($revision2['revision'], $revision['revision']);

        $collectionHandler->drop($collection);
    }

    /**
     *
     * test for getting the revision for a non existing collection
     */
    public function testGetRevisionWithException()
    {
        $collectionHandler = $this->collectionHandler;
        try {
            $collectionHandler->getRevision('nonExisting');
        } catch (\Exception $e) {
            static::assertEquals(404, $e->getCode());
        }
    }


    /**
     * test for creation, rename, and delete of a collection
     */
    public function testCreateRenameAndDeleteCollection()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collection        = $this->collection;
        $collectionHandler = $this->collectionHandler;


        $name = 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp;
        $collection->setName($name);

        $response = $collectionHandler->create($collection);

        static::assertTrue(is_numeric($response), 'Adding collection did not return an id!');

        $resultingCollection = $collectionHandler->get($name);

        $collectionHandler->rename(
            $resultingCollection,
            'ArangoDB_PHP_TestSuite_TestCollection_01_renamed' . '_' . static::$testsTimestamp
        );

        $resultingCollectionRenamed = $collectionHandler->get('ArangoDB_PHP_TestSuite_TestCollection_01_renamed' . '_' . static::$testsTimestamp);
        $newName                    = $resultingCollectionRenamed->getName();

        static::assertEquals(
            'ArangoDB_PHP_TestSuite_TestCollection_01_renamed' . '_' . static::$testsTimestamp, $newName, 'Collection was not renamed!'
        );
        $response = $collectionHandler->drop($resultingCollectionRenamed);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, rename, and delete of a collection with wrong encoding
     *
     * We expect an exception here:
     *
     * @expectedException \ArangoDBClient\ClientException
     *
     */
    public function testCreateRenameAndDeleteCollectionWithWrongEncoding()
    {
        $collection        = $this->collection;
        $collectionHandler = $this->collectionHandler;


        $name = 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp;
        $collection->setName($name);

        $response = $collectionHandler->create($collection);

        static::assertTrue(is_numeric($response), 'Adding collection did not return an id!');

        $resultingCollection = $collectionHandler->get($name);

        // inject wrong encoding
        $isoValue = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ArangoDB_PHP_TestSuite_TestCollection_01_renamedü');

        static::assertTrue($collectionHandler->rename($resultingCollection, $isoValue));


        $response = $collectionHandler->drop($resultingCollection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, get, and delete of a collection with waitForSync set to true
     */
    public function testCreateGetAndDeleteCollectionWithWaitForSyncTrueAndJournalSizeSet()
    {
        $collection        = $this->collection;
        $collectionHandler = $this->collectionHandler;
        $collection->setWaitForSync(true);
        $collection->setJournalSize(1024 * 1024 * 2);
        $resultingWaitForSyncAttribute = $collection->getWaitForSync();
        $resultingJournalSizeAttribute = $collection->getJournalSize();


        static::assertTrue($resultingWaitForSyncAttribute, 'WaitForSync should be true!');
        static::assertEquals(1024 * 1024 * 2, $resultingJournalSizeAttribute, 'JournalSize should be 2MB!');

        $name = 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp;
        $collection->setName($name);

        $collectionHandler->create($collection);

        // here we check the collectionHandler->getProperties function
        $properties = $collectionHandler->getProperties($collection->getName());
        static::assertObjectHasAttribute(
            '_waitForSync',
            $properties,
            'waiForSync field should exist, empty or with an id'
        );
        static::assertObjectHasAttribute(
            '_journalSize',
            $properties,
            'journalSize field should exist, empty or with an id'
        );

        // here we check the collectionHandler->unload() function
        // First fill it a bit to make sure it's loaded...
        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document);

        $arrayOfDocuments = $collectionHandler->getAllIds($collection->getName());

        static::assertTrue(
            is_array($arrayOfDocuments) && (count($arrayOfDocuments) === 2),
            'Should return an array of 2 document ids!'
        );

        //now check
        $unloadResult = $collectionHandler->unload($collection->getName());
        $unloadResult = $unloadResult->getJson();
        static::assertArrayHasKey('status', $unloadResult, 'status field should exist');
        static::assertTrue(
            $unloadResult['status'] === 4 || $unloadResult['status'] === 2,
            'Collection status should be 4 (in the process of being unloaded) or 2 (unloaded). Found: ' . $unloadResult['status'] . '!'
        );


        // here we check the collectionHandler->load() function
        $loadResult = $collectionHandler->load($collection->getName());
        $loadResult = $loadResult->getJson();
        static::assertArrayHasKey('status', $loadResult, 'status field should exist');
        static::assertEquals(
            3, $loadResult['status'], 'Collection status should be 3(loaded). Found: ' . $unloadResult['status'] . '!'
        );


        $resultingWaitForSyncAttribute = $collection->getWaitForSync();
        $resultingJournalSizeAttribute = $collection->getJournalSize();
        static::assertTrue($resultingWaitForSyncAttribute, 'Server waitForSync should return true!');
        static::assertEquals(1024 * 1024 * 2, $resultingJournalSizeAttribute, 'JournalSize should be 2MB!');

        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, get, and delete of a collection given its settings through createFrom[] and waitForSync set to true
     */
    public function testCreateGetAndDeleteCollectionThroughCreateFromArrayWithWaitForSyncTrue()
    {
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $response   = $collectionHandler->create($collection);

        $collectionHandler->get($response);

        $resultingAttribute = $collection->getWaitForSync();
        static::assertTrue($resultingAttribute, 'Server waitForSync should return true!');

        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation of documents, and removal by keys
     */
    public function testRemoveByKeys()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => false]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $keys   = [$documentId, $documentId2, $documentId3];
        $result = $collectionHandler->removeByKeys($collection->getName(), $keys);
        static::assertEquals(['removed' => 3, 'ignored' => 0], $result);
    }


    /**
     * test for removal by keys with unknown collection
     *
     * @expectedException \ArangoDBClient\ServerException
     */
    public function testRemoveByKeysCollectionNotFound()
    {
        $collectionHandler = $this->collectionHandler;

        $keys = ['foo'];
        $collectionHandler->removeByKeys('ThisDoesNotExist', $keys);
    }


    /**
     * test for creation of documents, and removal by keys
     */
    public function testRemoveByKeysNotFound()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => false]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $keys   = ['foo', 'bar', 'baz'];
        $result = $collectionHandler->removeByKeys($collection->getName(), $keys);
        static::assertEquals(['removed' => 0, 'ignored' => 3], $result);
    }


    /**
     * test for creation of documents, and removal by example, using an empty example
     */
    public function testCreateDocumentsAndRemoveByExampleEmptyExample()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $result = $collectionHandler->removeByExample($collection->getName(), []);
        static::assertEquals(3, $result);
    }


    /**
     * test for update by example, using an empty example
     */
    public function testCreateDocumentsAndUpdateByExampleEmptyExample()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $result = $collectionHandler->updateByExample($collection->getName(), [], ['foo' => 'bar']);
        static::assertEquals(3, $result);
    }


    /**
     * test for update by example, using an empty update example
     */
    public function testCreateDocumentsAndUpdateByExampleEmptyUpdateExample()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $result = $collectionHandler->updateByExample($collection->getName(), [], []);
        static::assertEquals(3, $result);
    }


    /**
     * test for replace by example, using an empty example
     */
    public function testCreateDocumentsAndReplaceByExampleEmptyExample()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $result = $collectionHandler->replaceByExample($collection->getName(), [], ['foo' => 'bar']);
        static::assertEquals(3, $result);
    }

    /**
     * test for replace by example, using an empty example
     */
    public function testCreateDocumentsAndReplaceByExampleEmptyReplaceExample()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $result = $collectionHandler->replaceByExample($collection->getName(), [], []);
        static::assertEquals(3, $result);
    }


    /**
     * test for query by example, using an empty example
     */
    public function testCreateDocumentsAndQueryByExampleEmptyExample()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $cursor = $collectionHandler->byExample($collection->getName(), []);
        static::assertEquals(
            3, $cursor->getCount(), 'should return 3.'
        );
    }


    /**
     * test for creation of documents, and removal by example
     */
    public function testCreateDocumentsWithCreateFromArrayAndRemoveByExample()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $result          = $collectionHandler->removeByExample($collection->getName(), $exampleDocument);
        static::assertSame(2, $result);
    }

    /**
     * test for creation of documents, and removal by example
     */
    public function testCreateDocumentsWithCreateFromArrayGetAsArrayAndRemoveByExample()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document      = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId    = $documentHandler->save($collection->getName(), $document);
        $documentArray = $document->getAll(['_includeInternals' => false]);

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $cursor          = $collectionHandler->byExample($collection->getName(), $exampleDocument, ['_flat' => true]);

        $array = $cursor->getAll();

        static::assertArrayHasKey('_key', $array[0]);
        static::assertArrayHasKey('_id', $array[0]);
        static::assertArrayHasKey('_rev', $array[0]);

        unset($array[0]['_key'], $array[0]['_id'], $array[0]['_rev']);

        static::assertSame($documentArray, $array[0]);
    }


    /**
     * test for creation of documents, and update and replace by example and finally removal by example
     */
    public function testCreateDocumentsWithCreateFromArrayUpdateReplaceAndRemoveByExample()
    {
        $this->collectionHandler = new CollectionHandler($this->connection);
        $documentHandler         = $this->documentHandler;
        $collectionHandler       = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $updateDocument  = Document::createFromArray(['someNewAttribute' => 'someNewValue']);

        $result = $collectionHandler->updateByExample($collection->getName(), $exampleDocument, $updateDocument);
        static::assertSame(2, $result);

        $exampleDocument = Document::createFromArray(['someAttribute' => 'someValue2']);
        $replaceDocument = Document::createFromArray(
            [
                'someAttribute'      => 'someValue2replaced',
                'someOtherAttribute' => 'someOtherValue2replaced'
            ]
        );
        $result          = $collectionHandler->replaceByExample(
            $collection->getName(),
            $exampleDocument,
            $replaceDocument
        );
        static::assertSame(1, $result);

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $result          = $collectionHandler->removeByExample($collection->getName(), $exampleDocument);
        static::assertSame(2, $result);
    }


    /**
     * test for creation of documents, and update and replace by example and finally removal by example
     */
    public function testCreateDocumentsFromArrayUpdateReplaceAndRemoveByExample()
    {
        $this->collectionHandler = new CollectionHandler($this->connection);
        $documentHandler         = $this->documentHandler;
        $collectionHandler       = $this->collectionHandler;


        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document = ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue'];

        $documentId = $documentHandler->save($collection->getName(), $document);
        static::assertTrue(is_numeric($documentId), 'Did not return an id!');


        $document2 = ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2'];


        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        static::assertTrue(is_numeric($documentId2), 'Did not return an id!');


        $document3 = ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue'];

        $documentId3 = $documentHandler->save($collection->getName(), $document3);
        static::assertTrue(is_numeric($documentId3), 'Did not return an id!');


        $exampleDocument = ['someOtherAttribute' => 'someOtherValue'];
        $updateDocument  = ['someNewAttribute' => 'someNewValue'];

        $result = $collectionHandler->updateByExample($collection->getName(), $exampleDocument, $updateDocument);
        static::assertSame(2, $result);


        $exampleDocument = ['someAttribute' => 'someValue2'];
        $replaceDocument =
            ['someAttribute' => 'someValue2replaced', 'someOtherAttribute' => 'someOtherValue2replaced'];

        $result = $collectionHandler->replaceByExample(
            $collection->getName(),
            $exampleDocument,
            $replaceDocument
        );
        static::assertSame(1, $result);


        $exampleDocument = ['someOtherAttribute' => 'someOtherValue'];
        $result          = $collectionHandler->removeByExample($collection->getName(), $exampleDocument);
        static::assertSame(2, $result);
    }


    /**
     * test for creation of documents, and update and replace by example and finally removal by example
     */
    public function testCreateDocumentsWithCreateFromArrayUpdateReplaceAndRemoveByExampleWithLimits()
    {
        $this->collectionHandler = new CollectionHandler($this->connection);
        $documentHandler         = $this->documentHandler;
        $collectionHandler       = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $updateDocument  = Document::createFromArray(['someNewAttribute' => 'someNewValue']);

        $result = $collectionHandler->updateByExample(
            $collection->getName(),
            $exampleDocument,
            $updateDocument,
            ['limit' => 1]
        );
        static::assertSame(1, $result);

        $exampleDocument = Document::createFromArray(['someAttribute' => 'someValue2']);
        $replaceDocument = Document::createFromArray(
            [
                'someAttribute'      => 'someValue2replaced',
                'someOtherAttribute' => 'someOtherValue2replaced'
            ]
        );
        $result          = $collectionHandler->replaceByExample(
            $collection->getName(),
            $exampleDocument,
            $replaceDocument,
            ['limit' => 2]
        );
        static::assertSame(1, $result);

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $result          = $collectionHandler->removeByExample(
            $collection->getName(),
            $exampleDocument,
            ['limit' => 1]
        );
        static::assertSame(1, $result);
    }


    /**
     * test for creation of documents, and update and replace by example and finally removal by example
     */
    public function testCreateDocumentsWithCreateFromArrayUpdateReplaceAndRemoveByExampleWithWaitForSync()
    {
        $this->collectionHandler = new CollectionHandler($this->connection);
        $documentHandler         = $this->documentHandler;
        $collectionHandler       = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $updateDocument  = Document::createFromArray(['someNewAttribute' => 'someNewValue']);

        $result = $collectionHandler->updateByExample(
            $collection->getName(),
            $exampleDocument,
            $updateDocument,
            ['waitForSync' => true]
        );
        static::assertSame(2, $result);

        $exampleDocument = Document::createFromArray(['someAttribute' => 'someValue2']);
        $replaceDocument = Document::createFromArray(
            [
                'someAttribute'      => 'someValue2replaced',
                'someOtherAttribute' => 'someOtherValue2replaced'
            ]
        );
        $result          = $collectionHandler->replaceByExample(
            $collection->getName(),
            $exampleDocument,
            $replaceDocument,
            ['waitForSync' => true]
        );
        static::assertSame(1, $result);

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $result          = $collectionHandler->removeByExample(
            $collection->getName(),
            $exampleDocument,
            ['waitForSync' => true]
        );
        static::assertSame(2, $result);
    }


    /**
     * test for creation of documents, and update and replace by example and finally removal by example
     */
    public function testCreateDocumentsWithCreateFromArrayUpdateReplaceAndRemoveByExampleWithKeepNull()
    {
        $this->collectionHandler = new CollectionHandler($this->connection);
        $documentHandler         = $this->documentHandler;
        $collectionHandler       = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $exampleDocument = Document::createFromArray(['someAttribute' => 'someValue2']);
        $updateDocument  = Document::createFromArray(
            ['someNewAttribute' => 'someNewValue', 'someOtherAttribute' => null]
        );

        $result = $collectionHandler->updateByExample(
            $collection->getName(),
            $exampleDocument,
            $updateDocument,
            ['keepNull' => false]
        );
        static::assertSame(1, $result);


        $exampleDocument = Document::createFromArray(['someNewAttribute' => 'someNewValue']);
        $cursor          = $collectionHandler->byExample($collection->getName(), $exampleDocument);
        static::assertEquals(
            1, $cursor->getCount(), 'should return 1.'
        );

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $result          = $collectionHandler->removeByExample(
            $collection->getName(),
            $exampleDocument,
            ['waitForSync' => true]
        );
        static::assertSame(2, $result);
    }


    /**
     * test for creation of documents, and removal by example
     */
    public function testCreateDocumentsWithCreateFromArrayAndRemoveByExampleWithLimit()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentId) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId), 'Did not return an id!');
        @list($collectionName, $documentId2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId2), 'Did not return an id!');
        @list($collectionName, $documentId3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentId3), 'Did not return an id!');

        $exampleDocument = Document::createFromArray(['someOtherAttribute' => 'someOtherValue']);
        $result          = $collectionHandler->removeByExample(
            $collection->getName(),
            $exampleDocument,
            ['limit' => 1]
        );
        static::assertSame(1, $result);
    }


    /**
     * test for import of documents, Headers-Values Style
     */
    public function testImportFromFileUsingHeadersAndValues()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collectionHandler = $this->collectionHandler;
        $result            = $collectionHandler->importFromFile(
            'ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp,
            __DIR__ . '/files_for_tests/import_file_header_values.txt',
            $options = ['createCollection' => true]
        );

        static::assertTrue($result['error'] === false && $result['created'] === 2);

        $statement = new Statement(
            $this->connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 1,
                'sanitize'  => true,
            ]
        );
        $query     = 'FOR u IN `ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp . '` SORT u._id ASC RETURN u';

        $statement->setQuery($query);

        $cursor = $statement->execute();

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertTrue(
            $resultingDocument[0]->getKey() === 'test1' && $resultingDocument[0]->firstName === 'Joe',
            'Document returned did not contain expected data.'
        );

        static::assertTrue(
            $resultingDocument[1]->getKey() === 'test2' && $resultingDocument[1]->firstName === 'Jane',
            'Document returned did not contain expected data.'
        );

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));
    }


    /**
     * test for import of documents, Line by Line Documents Style
     */
    public function testImportFromFileUsingDocumentsLineByLine()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collectionHandler = $this->collectionHandler;
        $result            = $collectionHandler->importFromFile(
            'ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp,
            __DIR__ . '/files_for_tests/import_file_line_by_line.txt',
            $options = ['createCollection' => true, 'type' => 'documents']
        );
        static::assertTrue($result['error'] === false && $result['created'] === 2);

        $statement = new Statement(
            $this->connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 2,
                'sanitize'  => true,
            ]
        );
        $query     = 'FOR u IN `ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp . '` SORT u._id ASC RETURN u';

        $statement->setQuery($query);

        $cursor = $statement->execute();

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertTrue(
            $resultingDocument[0]->getKey() === 'test1' && $resultingDocument[0]->firstName === 'Joe',
            'Document returned did not contain expected data.'
        );

        static::assertTrue(
            $resultingDocument[1]->getKey() === 'test2' && $resultingDocument[1]->firstName === 'Jane',
            'Document returned did not contain expected data.'
        );

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));
    }


    /**
     * test for import of documents, Line by Line result-set Style
     */
    public function testImportFromFileUsingResultSet()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collectionHandler = $this->collectionHandler;
        $result            = $collectionHandler->importFromFile(
            'ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp,
            __DIR__ . '/files_for_tests/import_file_resultset.txt',
            $options = ['createCollection' => true, 'type' => 'array']
        );
        static::assertTrue($result['error'] === false && $result['created'] === 2);

        $statement = new Statement(
            $this->connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 3,
                'sanitize'  => true,
            ]
        );
        $query     = 'FOR u IN `ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp . '` SORT u._id ASC RETURN u';

        $statement->setQuery($query);

        $cursor = $statement->execute();

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertEquals(
            2, $cursor->getCount(), 'should return 2.'
        );

        static::assertTrue(
            $resultingDocument[0]->getKey() === 'test1' && $resultingDocument[0]->firstName === 'Joe',
            'Document returned did not contain expected data.'
        );

        static::assertTrue(
            $resultingDocument[1]->getKey() === 'test2' && $resultingDocument[1]->firstName === 'Jane',
            'Document returned did not contain expected data.'
        );

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));
    }


    /**
     * test for import of documents by giving an array of documents
     */
    public function testImportFromArrayOfDocuments()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collectionHandler = $this->collectionHandler;

        $document1 = Document::createFromArray(
            [
                'firstName' => 'Joe',
                'lastName'  => 'Public',
                'age'       => 42,
                'gender'    => 'male',
                '_key'      => 'test1'
            ]
        );
        $document2 = Document::createFromArray(
            [
                'firstName' => 'Jane',
                'lastName'  => 'Doe',
                'age'       => 31,
                'gender'    => 'female',
                '_key'      => 'test2'
            ]
        );

        $data   = [$document1, $document2];
        $result = $collectionHandler->import(
            'ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp,
            $data,
            $options = ['createCollection' => true]
        );

        static::assertTrue($result['error'] === false && $result['created'] === 2);

        $statement = new Statement(
            $this->connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 4,
                'sanitize'  => true,
            ]
        );
        $query     = 'FOR u IN `ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp . '` SORT u._id ASC RETURN u';

        $statement->setQuery($query);

        $cursor = $statement->execute();

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertTrue(
            $resultingDocument[0]->getKey() === 'test1' && $resultingDocument[0]->firstName === 'Joe',
            'Document returned did not contain expected data.'
        );

        static::assertTrue(
            $resultingDocument[1]->getKey() === 'test2' && $resultingDocument[1]->firstName === 'Jane',
            'Document returned did not contain expected data.'
        );

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));
    }


    /**
     * test for import of documents by giving an array of documents
     */
    public function testImportFromStringWithValuesAndHeaders()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collectionHandler = $this->collectionHandler;

        $data = '[ "firstName", "lastName", "age", "gender", "_key"]
               [ "Joe", "Public", 42, "male", "test1" ]
               [ "Jane", "Doe", 31, "female", "test2" ]';

        $result = $collectionHandler->import(
            'ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp,
            $data,
            $options = ['createCollection' => true]
        );

        static::assertTrue($result['error'] === false && $result['created'] === 2);

        $statement = new Statement(
            $this->connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 5,
                'sanitize'  => true,
            ]
        );
        $query     = 'FOR u IN `ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp . '` SORT u._id ASC RETURN u';

        $statement->setQuery($query);

        $cursor = $statement->execute();

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertTrue(
            $resultingDocument[0]->getKey() === 'test1' && $resultingDocument[0]->firstName === 'Joe',
            'Document returned did not contain expected data.'
        );

        static::assertTrue(
            $resultingDocument[1]->getKey() === 'test2' && $resultingDocument[1]->firstName === 'Jane',
            'Document returned did not contain expected data.'
        );

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));
    }


    /**
     * test for import of documents by giving an array of documents
     */
    public function testImportFromStringUsingDocumentsLineByLine()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collectionHandler = $this->collectionHandler;

        $data = '{ "firstName" : "Joe", "lastName" : "Public", "age" : 42, "gender" : "male", "_key" : "test1"}
               { "firstName" : "Jane", "lastName" : "Doe", "age" : 31, "gender" : "female", "_key" : "test2"}';

        $result = $collectionHandler->import(
            'ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp,
            $data,
            $options = ['createCollection' => true, 'type' => 'documents']
        );

        static::assertTrue($result['error'] === false && $result['created'] === 2);

        $statement = new Statement(
            $this->connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 100,
                'sanitize'  => true,
            ]
        );
        $query     = 'FOR u IN `ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp . '` SORT u._id ASC RETURN u';

        $statement->setQuery($query);

        $cursor = $statement->execute();

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertTrue(
            $resultingDocument[0]->getKey() === 'test1' && $resultingDocument[0]->firstName === 'Joe',
            'Document returned did not contain expected data.'
        );

        static::assertTrue(
            $resultingDocument[1]->getKey() === 'test2' && $resultingDocument[1]->firstName === 'Jane',
            'Document returned did not contain expected data.'
        );

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));
    }


    /**
     * test for import of documents by giving an array of documents
     */
    public function testImportFromStringUsingDocumentsUsingResultset()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $collectionHandler = $this->collectionHandler;

        $data = '[{ "firstName" : "Joe", "lastName" : "Public", "age" : 42, "gender" : "male", "_key" : "test1"},
{ "firstName" : "Jane", "lastName" : "Doe", "age" : 31, "gender" : "female", "_key" : "test2"}]';

        $result = $collectionHandler->import(
            'ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp,
            $data,
            $options = ['createCollection' => true, 'type' => 'array']
        );

        static::assertTrue($result['error'] === false && $result['created'] === 2);

        $statement = new Statement(
            $this->connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 1000,
                'sanitize'  => true,
            ]
        );
        $query     = 'FOR u IN `ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp . '` SORT u._id ASC RETURN u';

        $statement->setQuery($query);

        $cursor = $statement->execute();

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertTrue(
            $resultingDocument[0]->getKey() === 'test1' && $resultingDocument[0]->firstName === 'Joe',
            'Document returned did not contain expected data.'
        );

        static::assertTrue(
            $resultingDocument[1]->getKey() === 'test2' && $resultingDocument[1]->firstName === 'Jane',
            'Document returned did not contain expected data.'
        );

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));
    }


    /**
     * test for creation, getAllIds, and delete of a collection given its settings through createFrom[]
     */
    public function testCreateGetAllIdsAndDeleteCollectionThroughCreateFromArray()
    {
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document);

        $arrayOfDocuments = $collectionHandler->getAllIds($collection->getName());

        static::assertTrue(
            is_array($arrayOfDocuments) && (count($arrayOfDocuments) === 2),
            'Should return an array of 2 document ids!'
        );

        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, all, and delete of a collection
     */
    public function testCreateAndAllAndDeleteCollection()
    {
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document);

        $cursor = $collectionHandler->all($collection->getName());

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));

        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, all with hiddenAttributes, and delete of a collection
     */
    public function testCreateAndIssueAllWithHiddenAttributesAndDeleteCollection()
    {
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document);

        $cursor = $collectionHandler->all(
            $collection->getName(), [
                '_ignoreHiddenAttributes' => false,
                '_hiddenAttributes'       => ['someOtherAttribute']
            ]
        );

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
            $doc                     = $resultingDocument[$key]->getAll();
            static::assertArrayNotHasKey('someOtherAttribute', $doc);
        }

        $cursor = $collectionHandler->all(
            $collection->getName(), [
                '_ignoreHiddenAttributes' => true,
                '_hiddenAttributes'       => ['someOtherAttribute']
            ]
        );

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
            $doc                     = $resultingDocument[$key]->getAll();
            static::assertArrayHasKey('someOtherAttribute', $doc);
        }


        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }

    /**
     * test for creation, all with hiddenAttributes but different Doc->GetAll options, and delete of a collection
     */
    public function testCreateAndIssueAllWithHiddenAttributesButDifferentDocGetAllOptionsAndDeleteCollection()
    {
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document);

        $cursor = $collectionHandler->all(
            $collection->getName(), [
                '_ignoreHiddenAttributes' => false,
                '_hiddenAttributes'       => ['someOtherAttribute']
            ]
        );

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
            $doc                     = $resultingDocument[$key]->getAll();
            static::assertArrayNotHasKey('someOtherAttribute', $doc);
        }

        $cursor = $collectionHandler->all(
            $collection->getName(), [
                '_ignoreHiddenAttributes' => false,
                '_hiddenAttributes'       => ['someOtherAttribute']
            ]
        );

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
            $doc                     = $resultingDocument[$key]->getAll(
                [
                    '_ignoreHiddenAttributes' => true
                ]
            );
            static::assertArrayHasKey('someOtherAttribute', $doc);
        }

        $cursor = $collectionHandler->all(
            $collection->getName(), [
                '_ignoreHiddenAttributes' => false,
                '_hiddenAttributes'       => ['someOtherAttribute']
            ]
        );

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
            $doc                     = $resultingDocument[$key]->getAll(
                [
                    '_hiddenAttributes' => []
                ]
            );
            static::assertArrayHasKey('someOtherAttribute', $doc);
        }

        $cursor = $collectionHandler->all(
            $collection->getName(), [
                '_ignoreHiddenAttributes' => true,
                '_hiddenAttributes'       => ['someOtherAttribute']
            ]
        );

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
            $doc                     = $resultingDocument[$key]->getAll();
            static::assertArrayHasKey('someOtherAttribute', $doc);
        }

        $cursor = $collectionHandler->all(
            $collection->getName(), [
                '_ignoreHiddenAttributes' => true,
                '_hiddenAttributes'       => []
            ]
        );

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
            $doc                     = $resultingDocument[$key]->getAll(
                [
                    '_ignoreHiddenAttributes' => false,
                    '_hiddenAttributes'       => ['someOtherAttribute']
                ]
            );
            static::assertArrayNotHasKey('someOtherAttribute', $doc);
        }

        $cursor = $collectionHandler->all(
            $collection->getName(), [
                '_ignoreHiddenAttributes' => true
            ]
        );

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
            $doc                     = $resultingDocument[$key]->getAll(
                [
                    '_ignoreHiddenAttributes' => false,
                    '_hiddenAttributes'       => ['someOtherAttribute']
                ]
            );

            static::assertArrayNotHasKey('someOtherAttribute', $doc);
        }


        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, all with limit, and delete of a collection
     */
    public function testCreateAndAllWithLimitAndDeleteCollection()
    {
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document);

        $cursor = $collectionHandler->all($collection->getName(), ['limit' => 1]);

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        // 2 Documents limited to 1, the result should be 1
        static::assertCount(1, $resultingDocument, 'Should be 1, was: ' . count($resultingDocument));

        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation, all with skip, and delete of a collection
     */
    public function testCreateAndAllWithSkipAndDeleteCollection()
    {
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        for ($i = 0; $i < 3; $i++) {
            $document = Document::createFromArray(
                ['someAttribute' => 'someValue ' . $i, 'someOtherAttribute' => 'someValue ' . $i]
            );
            $documentHandler->save($collection->getName(), $document);
        }

        $cursor = $collectionHandler->all($collection->getName(), ['skip' => 1]);

        $resultingDocument = null;

        foreach ($cursor as $key => $value) {
            $resultingDocument[$key] = $value;
        }

        // With 3 Documents and skipping 1, the result should be 2
        static::assertCount(2, $resultingDocument, 'Should be 2, was: ' . count($resultingDocument));

        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creating, filling with documents and truncating the collection.
     */
    public function testCreateFillAndTruncateCollection()
    {
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document);

        $arrayOfDocuments = $collectionHandler->getAllIds($collection->getName());

        static::assertTrue(
            is_array($arrayOfDocuments) && (count($arrayOfDocuments) === 2),
            'Should return an array of 2 document ids!'
        );

        //truncate, given the collection object
        $collectionHandler->truncate($collection);


        $document = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);

        $document = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document);

        $arrayOfDocuments = $collectionHandler->getAllIds($collection->getName());

        static::assertTrue(
            is_array($arrayOfDocuments) && (count($arrayOfDocuments) === 2),
            'Should return an array of 2 document ids!'
        );

        //truncate, given the collection id
        $collectionHandler->truncate($collection->getName());


        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test to set some attributes and get all attributes of the collection through getAll()
     */
    public function testGetAll()
    {
        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $result     = $collection->getAll();

        static::assertArrayHasKey('id', $result, 'Id field should exist, empty or with an id');
        static::assertEquals(
            'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, $result['name'], 'name should return ArangoDB_PHP_TestSuite_TestCollection_01!'
        );
        static::assertTrue($result['waitForSync'], 'waitForSync should return true!');
    }

    
    /**
     * test for creation of a hash index
     */
    public function testCreateHashIndex()
    {
        // set up collections, indexes and test-documents
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        $document1 = Document::createFromArray([ 'index' => 1 ]);
        $document2 = Document::createFromArray([ 'index' => 1 ]);
        $documentHandler->save($collection->getName(), $document1);
        $documentHandler->save($collection->getName(), $document2);

        $indexRes       = $collectionHandler->index($collection->getName(), 'hash', ['index']);
        static::assertArrayHasKey(
            'isNewlyCreated',
            $indexRes,
            'index creation result should have the isNewlyCreated key !'
        );

        // Clean up...
        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }
    
    
    /**
     * test for creation of a hash index, uniqueness violation
     */
    public function testCreateUniqueHashIndex()
    {
        // set up collections, indexes and test-documents
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $documentHandler = $this->documentHandler;

        $document1 = Document::createFromArray([ 'index' => 1 ]);
        $document2 = Document::createFromArray([ 'index' => 1 ]);
        $documentHandler->save($collection->getName(), $document1);
        $documentHandler->save($collection->getName(), $document2);

        try {
            $collectionHandler->index($collection->getName(), 'hash', ['index'], true);
        } catch (ServerException $e) {
            static::assertInstanceOf(
                ServerException::class,
                $e,
                'Exception thrown was not a ServerException!'
            );
            static::assertEquals(400, $e->getCode());
        }

        // Clean up...
        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }



    /**
     * test for creation of a skip-list indexed collection and querying by range (first level and nested), with closed, skip and limit options
     */

    public function testCreateSkipListIndexedCollectionAddDocumentsAndQueryRange()
    {
        // set up collections, indexes and test-documents
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $indexRes       = $collectionHandler->index($collection->getName(), 'skiplist', ['index']);
        $nestedIndexRes = $collectionHandler->index($collection->getName(), 'skiplist', ['nested.index']);
        static::assertArrayHasKey(
            'isNewlyCreated',
            $indexRes,
            'index creation result should have the isNewlyCreated key !'
        );
        static::assertArrayHasKey(
            'isNewlyCreated',
            $nestedIndexRes,
            'index creation result should have the isNewlyCreated key !'
        );


        $documentHandler = $this->documentHandler;

        $document1 = Document::createFromArray(
            [
                'index'              => 2,
                'someOtherAttribute' => 'someValue2',
                'nested'             => [
                    'index'                => 3,
                    'someNestedAttribute3' => 'someNestedValue3'
                ]
            ]
        );
        $documentHandler->save($collection->getName(), $document1);
        $document2 = Document::createFromArray(
            [
                'index'              => 1,
                'someOtherAttribute' => 'someValue1',
                'nested'             => [
                    'index'                => 2,
                    'someNestedAttribute3' => 'someNestedValue2'
                ]
            ]
        );
        $documentHandler->save($collection->getName(), $document2);

        $document3 = Document::createFromArray(
            [
                'index'              => 3,
                'someOtherAttribute' => 'someValue3',
                'nested'             => [
                    'index'                => 1,
                    'someNestedAttribute3' => 'someNestedValue1'
                ]
            ]
        );
        $documentHandler->save($collection->getName(), $document3);


        // first level attribute range test
        $rangeResult = $collectionHandler->range($collection->getName(), 'index', 1, 2, ['closed' => false]);
        $resultArray = $rangeResult->getAll();
        static::assertSame(1, $resultArray[0]->index, 'This value should be 1 !');
        static::assertArrayNotHasKey(1, $resultArray, 'Should not have a second key !');


        $rangeResult = $collectionHandler->range($collection->getName(), 'index', 2, 3, ['closed' => true]);
        $resultArray = $rangeResult->getAll();
        static::assertSame(2, $resultArray[0]->index, 'This value should be 2 !');
        static::assertSame(3, $resultArray[1]->index, 'This value should be 3 !');


        $rangeResult = $collectionHandler->range(
            $collection->getName(),
            'index',
            2,
            3,
            ['closed' => true, 'limit' => 1]
        );
        $resultArray = $rangeResult->getAll();
        static::assertSame(2, $resultArray[0]->index, 'This value should be 2 !');
        static::assertArrayNotHasKey(1, $resultArray, 'Should not have a second key !');


        $rangeResult = $collectionHandler->range(
            $collection->getName(),
            'index',
            2,
            3,
            ['closed' => true, 'skip' => 1]
        );
        $resultArray = $rangeResult->getAll();
        static::assertSame(3, $resultArray[0]->index, 'This value should be 3 !');
        static::assertArrayNotHasKey(1, $resultArray, 'Should not have a second key !');


        // nested attribute range test
        $rangeResult = $collectionHandler->range($collection->getName(), 'nested.index', 1, 2, ['closed' => false]);
        $resultArray = $rangeResult->getAll();
        static::assertSame(1, $resultArray[0]->nested['index'], 'This value should be 1 !');
        static::assertArrayNotHasKey(1, $resultArray, 'Should not have a second key !');


        $rangeResult = $collectionHandler->range($collection->getName(), 'nested.index', 2, 3, ['closed' => true]);
        $resultArray = $rangeResult->getAll();
        static::assertSame(2, $resultArray[0]->nested['index'], 'This value should be 2 !');
        static::assertSame(3, $resultArray[1]->nested['index'], 'This value should be 3 !');


        $rangeResult = $collectionHandler->range(
            $collection->getName(),
            'nested.index',
            2,
            3,
            ['closed' => true, 'limit' => 1]
        );
        $resultArray = $rangeResult->getAll();
        static::assertSame(2, $resultArray[0]->nested['index'], 'This value should be 2 !');
        static::assertArrayNotHasKey(1, $resultArray, 'Should not have a second key !');


        $rangeResult = $collectionHandler->range(
            $collection->getName(),
            'nested.index',
            2,
            3,
            ['closed' => true, 'skip' => 1]
        );
        $resultArray = $rangeResult->getAll();
        static::assertSame(3, $resultArray[0]->nested['index'], 'This value should be 3 !');
        static::assertArrayNotHasKey(1, $resultArray, 'Should not have a second key !');


        // Clean up...
        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation of a geo indexed collection and querying by near, with distance, skip and limit options
     */
    public function testCreateGeoIndexedCollectionAddDocumentsAndQueryNear()
    {
        // set up collections, indexes and test-documents
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $indexRes = $collectionHandler->index($collection->getName(), 'geo', ['loc']);
        static::assertArrayHasKey(
            'isNewlyCreated',
            $indexRes,
            'index creation result should have the isNewlyCreated key !'
        );


        $documentHandler = $this->documentHandler;

        $document1 = Document::createFromArray(['loc' => [0, 0], 'someOtherAttribute' => '0 0']);
        $documentHandler->save($collection->getName(), $document1);
        $document2 = Document::createFromArray(['loc' => [1, 1], 'someOtherAttribute' => '1 1']);
        $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(['loc' => [+30, -30], 'someOtherAttribute' => '30 -30']);
        $documentId3 = $documentHandler->save($collection->getName(), $document3);
        $documentHandler->getById($collection->getName(), $documentId3);


        $rangeResult = $collectionHandler->near($collection->getName(), 0, 0);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 0 && $resultArray[0]->loc[1] === 0,
            'This value should be 0 0!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertTrue(
            $resultArray[1]->loc[0] === 1 && $resultArray[1]->loc[1] === 1,
            'This value should be 1 1!, is :' . $resultArray[1]->loc[0] . ' ' . $resultArray[1]->loc[1]
        );


        $rangeResult = $collectionHandler->near($collection->getName(), 0, 0, ['distance' => 'distance']);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 0 && $resultArray[0]->loc[1] === 0,
            'This value should be 0 0 !, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertTrue(
            $resultArray[1]->loc[0] === 1 && $resultArray[1]->loc[1] === 1,
            'This value should be 1 1!, is :' . $resultArray[1]->loc[0] . ' ' . $resultArray[1]->loc[1]
        );
        static::assertTrue(
            $resultArray[2]->loc[0] === 30 && $resultArray[2]->loc[1] === -30,
            'This value should be 30 30!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertSame(
            0, $resultArray[0]->distance, 'This value should be 0 ! It is :' . $resultArray[0]->distance
        );


        $rangeResult = $collectionHandler->near($collection->getName(), 0, 0, ['limit' => 1]);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 0 && $resultArray[0]->loc[1] === 0,
            'This value should be 0 0!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertArrayNotHasKey(1, $resultArray, 'Should not have a second key !');


        $rangeResult = $collectionHandler->near($collection->getName(), 0, 0, ['skip' => 1]);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 1 && $resultArray[0]->loc[1] === 1,
            'This value should be 1 1!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertTrue(
            $resultArray[1]->loc[0] === 30 && $resultArray[1]->loc[1] === -30,
            'This value should be 30 30!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertArrayNotHasKey(2, $resultArray, 'Should not have a third key !');


        $rangeResult = $collectionHandler->near($collection->getName(), +30, -30);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 30 && $resultArray[0]->loc[1] === -30,
            'This value should be 30 30!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertTrue(
            $resultArray[1]->loc[0] === 1 && $resultArray[1]->loc[1] === 1,
            'This value should be 1 1!, is :' . $resultArray[1]->loc[0] . ' ' . $resultArray[1]->loc[1]
        );
        static::assertTrue(
            $resultArray[2]->loc[0] === 0 && $resultArray[2]->loc[1] === 0,
            'This value should be 0 0!, is :' . $resultArray[1]->loc[0] . ' ' . $resultArray[1]->loc[1]
        );


        // Clean up...
        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation of a geo indexed collection and querying by within, with distance, skip and limit options
     */
    public function testCreateGeoIndexedCollectionAddDocumentsAndQueryWithin()
    {
        // set up collections, indexes and test-documents
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $indexRes = $collectionHandler->index($collection->getName(), 'geo', ['loc']);
        static::assertArrayHasKey(
            'isNewlyCreated',
            $indexRes,
            'index creation result should have the isNewlyCreated key !'
        );


        $documentHandler = $this->documentHandler;

        $document1 = Document::createFromArray(['loc' => [0, 0], 'someOtherAttribute' => '0 0']);
        $documentHandler->save($collection->getName(), $document1);
        $document2 = Document::createFromArray(['loc' => [1, 1], 'someOtherAttribute' => '1 1']);
        $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(['loc' => [+30, -30], 'someOtherAttribute' => '30 -30']);
        $documentId3 = $documentHandler->save($collection->getName(), $document3);
        $documentHandler->getById($collection->getName(), $documentId3);


        $rangeResult = $collectionHandler->within($collection->getName(), 0, 0, 0.00001);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 0 && $resultArray[0]->loc[1] === 0,
            'This value should be 0 0!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );


        $rangeResult = $collectionHandler->within(
            $collection->getName(),
            0,
            0,
            200 * 1000,
            ['distance' => 'distance']
        );
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 0 && $resultArray[0]->loc[1] === 0,
            'This value should be 0 0 !, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertTrue(
            $resultArray[1]->loc[0] === 1 && $resultArray[1]->loc[1] === 1,
            'This value should be 1 1!, is :' . $resultArray[1]->loc[0] . ' ' . $resultArray[1]->loc[1]
        );
        static::assertArrayNotHasKey(2, $resultArray, 'Should not have a third key !');
        static::assertSame(0, $resultArray[0]->distance, 'This value should be 0 ! It is :' . $resultArray[0]->distance);


        $rangeResult = $collectionHandler->within($collection->getName(), 0, 0, 200 * 1000, ['limit' => 1]);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 0 && $resultArray[0]->loc[1] === 0,
            'This value should be 0 0!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertArrayNotHasKey(1, $resultArray, 'Should not have a second key !');


        $rangeResult = $collectionHandler->within($collection->getName(), 0, 0, 20000 * 1000, ['skip' => 1]);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 1 && $resultArray[0]->loc[1] === 1,
            'This value should be 1 1!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertTrue(
            $resultArray[1]->loc[0] === 30 && $resultArray[1]->loc[1] === -30,
            'This value should be 30 30!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertArrayNotHasKey(2, $resultArray, 'Should not have a third key !');


        $rangeResult = $collectionHandler->within($collection->getName(), +30, -30, 20000 * 1000);
        $resultArray = $rangeResult->getAll();
        static::assertTrue(
            $resultArray[0]->loc[0] === 30 && $resultArray[0]->loc[1] === -30,
            'This value should be 30 30!, is :' . $resultArray[0]->loc[0] . ' ' . $resultArray[0]->loc[1]
        );
        static::assertTrue(
            $resultArray[1]->loc[0] === 1 && $resultArray[1]->loc[1] === 1,
            'This value should be 1 1!, is :' . $resultArray[1]->loc[0] . ' ' . $resultArray[1]->loc[1]
        );
        static::assertTrue(
            $resultArray[2]->loc[0] === 0 && $resultArray[2]->loc[1] === 0,
            'This value should be 0 0!, is :' . $resultArray[1]->loc[0] . ' ' . $resultArray[1]->loc[1]
        );


        // Clean up...
        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for creation of a fulltext indexed collection and querying by within, with distance, skip and limit options
     */
    public function testCreateFulltextIndexedCollectionAddDocumentsAndQuery()
    {
        // set up collections and index
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $indexRes = $collectionHandler->index($collection->getName(), 'fulltext', ['name']);
        static::assertArrayHasKey(
            'isNewlyCreated',
            $indexRes,
            'index creation result should have the isNewlyCreated key !'
        );

        // Check if the index is returned in the indexes of the collection
        $indexes = $collectionHandler->getIndexes($collection->getName());
        static::assertSame('name', $indexes['indexes'][1]['fields'][0], 'The index should be on field "name"!');

        // Drop the index
        $collectionHandler->dropIndex($indexes['indexes'][1]['id']);
        $indexes = $collectionHandler->getIndexes($collection->getName());

        // Check if the index is not in the indexes of the collection anymore
        static::assertArrayNotHasKey(1, $indexes['indexes'], 'There should not be an index on field "name"!');

        // Clean up...
        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * Test if we can create a full text index with options, on a collection
     */
    public function testCreateFulltextIndexedCollectionWithOptions()
    {
        // set up collections and index
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $indexRes = $collectionHandler->index(
            $collection->getName(),
            'fulltext',
            ['name'],
            false,
            ['minLength' => 10]
        );

        static::assertArrayHasKey(
            'isNewlyCreated',
            $indexRes,
            'index creation result should have the isNewlyCreated key !'
        );

        static::assertArrayHasKey('minLength', $indexRes, 'index creation result should have a minLength key!');

        static::assertEquals(
            10,
            $indexRes['minLength'],
            'index created does not have the same minLength as the one sent!'
        );

        // Check if the index is returned in the indexes of the collection
        $indexes = $collectionHandler->getIndexes($collection->getName());
        static::assertSame('name', $indexes['indexes'][1]['fields'][0], 'The index should be on field "name"!');

        // Drop the index
        $collectionHandler->dropIndex($indexes['indexes'][1]['id']);
        $indexes = $collectionHandler->getIndexes($collection->getName());

        // Check if the index is not in the indexes of the collection anymore
        static::assertArrayNotHasKey(1, $indexes['indexes'], 'There should not be an index on field "name"!');

        // Clean up...
        $response = $collectionHandler->drop($collection);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * Test getting a random document from the collection
     */
    public function testAnyDocumentInCollection()
    {
        // set up collections and documents
        $collectionHandler = $this->collectionHandler;
        $documentHandler   = $this->documentHandler;

        $collection = Collection::createFromArray(['name' => 'ArangoDB_PHP_TestSuite_TestCollection_Any' . '_' . static::$testsTimestamp]);
        $collectionHandler->create($collection);

        $document1 = new Document();
        $document1->set('message', 'message1');

        $documentHandler->save($collection->getName(), $document1);

        $document2 = new Document();
        $document2->set('message', 'message2');

        $documentHandler->save($collection->getName(), $document2);

        $document3 = new Document();
        $document3->set('message', 'message3');

        $documentHandler->save($collection->getName(), $document3);

        //Now, let's try to query any document
        $document = $collectionHandler->any($collection->getName());
        static::assertContains(
            $document->get('message'),
            ['message1', 'message2', 'message3'],
            'A document that was not part of the collection was retrieved!'
        );

        //Let's try another random document
        $document = $collectionHandler->any($collection->getName());
        static::assertContains(
            $document->get('message'),
            ['message1', 'message2', 'message3'],
            'A document that was not part of the collection was retrieved!'
        );

        $collectionHandler->drop($collection->getName());
    }


    /**
     * Test getting a random document from a collection that does not exist
     */
    public function testAnyDocumentInNonExistentCollection()
    {
        $collectionHandler = $this->collectionHandler;

        //To be safe, we need to make sure the collection definitely doesn't exist,
        //so, if it exists, delete it.
        try {
            $collectionHandler->drop('collection_that_does-not_exist');
        } catch (Exception $e) {
            //Ignore the exception.
        }

        try {
            //Let's try to get a random document
            $collectionHandler->any('collection_that_does_not_exist');
        } catch (ServerException $e) {
            static::assertInstanceOf(
                ServerException::class,
                $e,
                'Exception thrown was not a ServerException!'
            );
            static::assertEquals(404, $e->getCode(), 'Error code was not a 404!');
        }
    }


    /**
     * Test getting a random document from an empty collection
     */
    public function testAnyDocumentInAnEmptyCollection()
    {

        $collectionHandler = $this->collectionHandler;

        try {
            $collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_Any_Empty');
        } catch (Exception $e) {
            //Ignore
        }

        $collectionHandler->create('ArangoDB_PHP_TestSuite_TestCollection_Any_Empty');

        $any = $collectionHandler->any('ArangoDB_PHP_TestSuite_TestCollection_Any_Empty');

        static::assertNull($any, 'any() on an empty collection should return null.');

        $collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_Any_Empty');
    }


    /**
     * test for fulltext queries
     */
    public function testFulltextQuery()
    {
        $this->collectionHandler = new CollectionHandler($this->connection);
        $documentHandler         = $this->documentHandler;
        $collectionHandler       = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => true]
        );
        $collectionHandler->create($collection);
        $document = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document);
        $document2 = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentHandler->save($collection->getName(), $document2);
        $document3 = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentHandler->save($collection->getName(), $document3);
        // First we test without a fulltext index and expect a 400
        try {
            $collectionHandler->fulltext(
                $collection->getName(),
                'someOtherAttribute',
                'someOtherValue'
            );
        } catch (Exception $e) {

        }
        static::assertSame(400, $e->getCode());

        // Now we create an index
        $fulltextIndexId = $collectionHandler->createFulltextIndex($collection->getName(), ['someOtherAttribute']);
        $fulltextIndexId = $fulltextIndexId['id'];
        $cursor          = $collectionHandler->fulltext(
            $collection->getName(),
            'someOtherAttribute',
            'someOtherValue',
            ['index' => $fulltextIndexId]
        );

        $m = $cursor->getMetadata();
        static::assertEquals(2, $m['count']);
        static::assertEquals(false, $m['hasMore']);

        // Now we pass some options
        $cursor = $collectionHandler->fulltext(
            $collection->getName(),
            'someOtherAttribute',
            'someOtherValue',
            ['index' => $fulltextIndexId, 'skip' => 1,]
        );

        $m = $cursor->getMetadata();
        static::assertEquals(1, $m['count']);
        static::assertEquals(false, $m['hasMore']);

        $cursor = $collectionHandler->fulltext(
            $collection->getName(),
            'someOtherAttribute',
            'someOtherValue',
            ['batchSize' => 1]
        );

        $m = $cursor->getMetadata();
        static::assertEquals(2, $m['count']);
        static::assertCount(1, $m['result']);
        static::assertEquals(true, $m['hasMore']);

    }


    /**
     * test bulk document lookups
     */
    public function testLookupByKeys()
    {
        $documentHandler   = $this->documentHandler;
        $collectionHandler = $this->collectionHandler;

        $collection = Collection::createFromArray(
            ['name' => 'ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, 'waitForSync' => false]
        );
        $collectionHandler->create($collection);
        $document    = Document::createFromArray(
            ['someAttribute' => 'someValue1', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId  = $documentHandler->save($collection->getName(), $document);
        $document2   = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $documentId2 = $documentHandler->save($collection->getName(), $document2);
        $document3   = Document::createFromArray(
            ['someAttribute' => 'someValue3', 'someOtherAttribute' => 'someOtherValue']
        );
        $documentId3 = $documentHandler->save($collection->getName(), $document3);

        @list($collectionName, $documentKey) = explode('/', $documentId);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentKey), 'Did not return an id!');
        @list($collectionName, $documentKey2) = explode('/', $documentId2);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentKey2), 'Did not return an id!');
        @list($collectionName, $documentKey3) = explode('/', $documentId3);
        static::assertTrue($collection->getName() === $collectionName && is_numeric($documentKey3), 'Did not return an id!');

        $keys   = [$documentId, $documentId2, $documentId3];
        $result = $collectionHandler->lookupByKeys($collection->getName(), $keys);
        static::assertCount(3, $result);

        $document = $result[0];
        static::assertInstanceOf(
            Document::class,
            $document,
            'Object was not a Document!'
        );

        static::assertEquals($documentId, $document->getId());

        static::assertEquals('someValue1', $document->someAttribute);
        static::assertEquals('someOtherValue', $document->someOtherAttribute);

        $document = $result[1];
        static::assertInstanceOf(
            Document::class,
            $document,
            'Object was not a Document!'
        );

        static::assertEquals($documentId2, $document->getId());

        static::assertEquals('someValue2', $document->someAttribute);
        static::assertEquals('someOtherValue2', $document->someOtherAttribute);

        $document = $result[2];
        static::assertInstanceOf(
            Document::class,
            $document,
            'Object was not a Document!'
        );

        static::assertEquals($documentId3, $document->getId());

        static::assertEquals('someValue3', $document->someAttribute);
        static::assertEquals('someOtherValue', $document->someOtherAttribute);
    }

    /**
     * test for lookup by keys with unknown collection
     *
     * @expectedException \ArangoDBClient\ServerException
     */
    public function testLookupByCollectionNotFound()
    {
        $collectionHandler = $this->collectionHandler;

        $keys = ['foo'];
        $collectionHandler->lookupByKeys('ThisDoesNotExist', $keys);
    }

    /**
     * Test tear-down
     */
    public function tearDown()
    {
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_02' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_ImportCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }
        try {
            $this->collectionHandler->drop('_ArangoDB_PHP_TestSuite_TestCollection_01');
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_Any');
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        unset($this->collectionHandler, $this->collection, $this->connection);
    }
}
