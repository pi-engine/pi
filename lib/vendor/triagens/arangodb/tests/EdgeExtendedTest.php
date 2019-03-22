<?php
/**
 * ArangoDB PHP client testsuite
 * File: EdgeExtendedTest.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

/**
 * Class EdgeExtendedTest
 *
 * @property Connection        $connection
 * @property Collection        $collection
 * @property Collection        $edgeCollection
 * @property CollectionHandler $collectionHandler
 * @property DocumentHandler   $documentHandler
 * @property EdgeHandler       $edgeHandler
 *
 * @package ArangoDBClient
 */
class EdgeExtendedTest extends
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
        $this->collection->setName('ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp);
        $this->collectionHandler->create($this->collection);

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp);
        } catch (Exception $e) {
            //Silence the exception
        }

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        } catch (Exception $e) {
            //Silence the exception
        }

        $this->edgeHandler    = new EdgeHandler($this->connection);
        $this->edgeCollection = new Collection();
        $this->edgeCollection->setName('ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp);
        $this->edgeCollection->set('type', 3);
        $this->collectionHandler->create($this->edgeCollection);
        $this->documentCollection = new Collection();
        $this->documentCollection->setName('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        $this->collectionHandler->create($this->documentCollection);
    }


    /**
     * Test for correct exception codes if non-existent objects are tried to be gotten, replaced, updated or removed
     */
    public function testGetReplaceUpdateAndRemoveOnNonExistentObjects()
    {
        // Setup objects
        $edgeHandler = $this->edgeHandler;
        $edge        = Edge::createFromArray(
            [
                'someAttribute'      => 'someValue',
                'someOtherAttribute' => 'someOtherValue',
                'someThirdAttribute' => 'someThirdValue'
            ]
        );


        // Try to get a non-existent edge out of a nonexistent collection
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $edgeHandler->get('nonExistentCollection', 'nonexistentId');
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to get a non-existent edge out of an existent collection
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $edgeHandler->get($this->collection->getName(), 'nonexistentId');
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to update a non-existent edge
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $edgeHandler->updateById($this->collection->getName(), 'nonexistentId', $edge);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to replace a non-existent edge
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $edgeHandler->replaceById($this->collection->getName(), 'nonexistentId', $edge);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to remove a non-existent edge
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $edgeHandler->removeById($this->collection->getName(), 'nonexistentId');
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());
    }


    /**
     * test for updating a edge using update()
     */
    public function testUpdateEdge()
    {
        $connection  = $this->connection;
        $edgeHandler = new EdgeHandler($connection);


        $edgeCollection = $this->edgeCollection;

        $document1       = new Document();
        $document2       = new Document();
        $documentHandler = new DocumentHandler($connection);

        $edgeDocument = new Edge();

        $document1->someAttribute = 'someValue1';
        $document2->someAttribute = 'someValue2';


        $documentHandler->save('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, $document1);
        $documentHandler->save('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, $document2);
        $documentHandle1 = $document1->getHandle();
        $documentHandle2 = $document2->getHandle();


        $edgeDocument->set('label', 'knows');
        $edgeId = $edgeHandler->saveEdge(
            $edgeCollection->getName(),
            $documentHandle1,
            $documentHandle2,
            $edgeDocument
        );
        @list($collectionName, $edgeId) = explode('/', $edgeId);
        static::assertSame($collectionName, $edgeCollection->getName(), 'Did not return an id!');
        static::assertTrue(is_numeric($edgeId), 'Did not return an id!');

        $edgeDocument->set('labels', 'anything');
        $result = $edgeHandler->update($edgeDocument);

        static::assertTrue($result);

        $resultingEdge = $edgeHandler->get($edgeCollection->getId(), $edgeId);
        static::assertObjectHasAttribute('_id', $resultingEdge, '_id field should exist, empty or with an id');

        static::assertEquals(
            'anything', $resultingEdge->labels, 'Should be :anything, is: ' . $resultingEdge->labels
        );
        static::assertEquals(
            'knows', $resultingEdge->label, 'Should be :knows, is: ' . $resultingEdge->label
        );
        $response = $edgeHandler->remove($resultingEdge);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for updating a edge using update() with wrong encoding
     * We expect an exception here:
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testUpdateEdgeWithWrongEncoding()
    {
        $edgeHandler = $this->edgeHandler;

        $edge   = Edge::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $edgeId = $edgeHandler->save($this->collection->getId(), $edge);
        $edgeHandler->get($this->collection->getId(), $edgeId);
        static::assertTrue(is_numeric($edgeId), 'Did not return an id!');

        $patchEdge = new Edge();
        $patchEdge->set('_id', $edge->getHandle());
        $patchEdge->set('_rev', $edge->getRevision());

        // inject wrong encoding
        $isoValue = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'someWrongEncodedValueü');

        $patchEdge->set('someOtherAttribute', $isoValue);
        $result = $edgeHandler->update($patchEdge);

        static::assertTrue($result);

        $resultingEdge = $edgeHandler->get($this->collection->getId(), $edgeId);
        static::assertObjectHasAttribute('_id', $resultingEdge, '_id field should exist, empty or with an id');

        static::assertEquals(
            'someValue', $resultingEdge->someAttribute, 'Should be :someValue, is: ' . $resultingEdge->someAttribute
        );
        static::assertEquals(
            'someOtherValue2', $resultingEdge->someOtherAttribute, 'Should be :someOtherValue2, is: ' . $resultingEdge->someOtherAttribute
        );
        $response = $edgeHandler->remove($resultingEdge);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for updating a edge using update()
     */
    public function testUpdateEdgeDoNotKeepNull()
    {
        $connection  = $this->connection;
        $edgeHandler = new EdgeHandler($connection);


        $edgeCollection = $this->edgeCollection;

        $document1       = new Document();
        $document2       = new Document();
        $documentHandler = new DocumentHandler($connection);

        $edgeDocument = new Edge();

        $document1->someAttribute = 'someValue1';
        $document2->someAttribute = 'someValue2';


        $documentHandler->save('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, $document1);
        $documentHandler->save('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, $document2);
        $documentHandle1 = $document1->getHandle();
        $documentHandle2 = $document2->getHandle();


        $edgeDocument->set('label', null);
        $edgeId = $edgeHandler->saveEdge(
            $edgeCollection->getName(),
            $documentHandle1,
            $documentHandle2,
            $edgeDocument
        );
        @list($collectionName, $edgeId) = explode('/', $edgeId);
        static::assertSame($collectionName, $edgeCollection->getName(), 'Did not return an id!');
        static::assertTrue(is_numeric($edgeId), 'Did not return an id!');

        $edgeDocument->set('labels', 'anything');
        $result = $edgeHandler->update($edgeDocument, ['keepNull' => false]);

        static::assertTrue($result);

        $resultingEdge = $edgeHandler->get($edgeCollection->getId(), $edgeId);
        static::assertObjectHasAttribute('_id', $resultingEdge, '_id field should exist, empty or with an id');

        static::assertEquals(
            null, $resultingEdge->label, 'Should be : null, is: ' . $resultingEdge->label
        );
        static::assertEquals(
            'anything', $resultingEdge->labels, 'Should be :anything, is: ' . $resultingEdge->labels
        );
        $response = $edgeHandler->remove($resultingEdge);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for replacing a edge using replace()
     */
    public function testReplaceEdge()
    {
        $connection  = $this->connection;
        $edgeHandler = new EdgeHandler($connection);


        $edgeCollection = $this->edgeCollection;

        $document1       = new Document();
        $document2       = new Document();
        $documentHandler = new DocumentHandler($connection);

        $edgeDocument = new Edge();

        $document1->someAttribute = 'someValue1';
        $document2->someAttribute = 'someValue2';


        $documentHandler->save('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, $document1);
        $documentHandler->save('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp, $document2);
        $documentHandle1 = $document1->getHandle();
        $documentHandle2 = $document2->getHandle();


        $edgeDocument->set('label', null);
        $edgeDocument->set('labelt', 'as');
        $edgeId = $edgeHandler->saveEdge(
            $edgeCollection->getName(),
            $documentHandle1,
            $documentHandle2,
            $edgeDocument
        );

        @list($collectionName, $edgeId) = explode('/', $edgeId);
        static::assertSame($collectionName, $edgeCollection->getName(), 'Did not return an id!');
        static::assertTrue(is_numeric($edgeId), 'Did not return an id!');

        $edgePutDocument = new Edge();
        $edgePutDocument->set('_id', $edgeDocument->getHandle());
        $edgePutDocument->set('_rev', $edgeDocument->getRevision());
        $edgePutDocument->set('labels', 'as');
        $edgePutDocument->setFrom($documentHandle1);
        $edgePutDocument->setTo($documentHandle2);
        $result = $edgeHandler->replace($edgePutDocument);

        static::assertTrue($result);
        $resultingEdge = $edgeHandler->get($edgeCollection->getId(), $edgeId);

        static::assertObjectHasAttribute('_id', $resultingEdge, '_id field should exist, empty or with an id');

        static::assertEquals(
            null, $resultingEdge->label, 'Should be :null, is: ' . $resultingEdge->label
        );
        static::assertEquals(
            null, $resultingEdge->labelt, 'Should be :null, is: ' . $resultingEdge->labelt
        );

        static::assertEquals('as', $resultingEdge->labels);

        $response = $edgeHandler->remove($resultingEdge);
        static::assertTrue($response, 'Delete should return true!');
    }


    /**
     * test for replacing a edge using replace() with wrong encoding
     * We expect an exception here:
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testReplaceEdgeWithWrongEncoding()
    {
        $edgeHandler = $this->edgeHandler;

        $edge   = Edge::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $edgeId = $edgeHandler->save($this->collection->getId(), $edge);

        static::assertTrue(is_numeric($edgeId), 'Did not return an id!');

        // inject wrong encoding
        $isoKey   = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'someWrongEncodedAttribute');
        $isoValue = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'someWrongEncodedValueü');

        $edge->set($isoKey, $isoValue);
        $edge->set('someOtherAttribute', 'someOtherValue2');
        $result = $edgeHandler->replace($edge);

        static::assertTrue($result);
        $resultingEdge = $edgeHandler->get($this->collection->getId(), $edgeId);

        static::assertObjectHasAttribute('_id', $resultingEdge, '_id field should exist, empty or with an id');

        static::assertEquals(
            'someValue2', $resultingEdge->someAttribute, 'Should be :someValue2, is: ' . $resultingEdge->someAttribute
        );
        static::assertEquals(
            'someOtherValue2', $resultingEdge->someOtherAttribute, 'Should be :someOtherValue2, is: ' . $resultingEdge->someOtherAttribute
        );

        $response = $edgeHandler->remove($resultingEdge);
        static::assertTrue($response, 'Delete should return true!');
    }


    public function tearDown()
    {
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestEdgeCollection_02' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }


        unset($this->collectionHandler, $this->collection, $this->connection, $this->edgeHandler, $this->edgeCollection, $this->documentCollection);
    }
}
