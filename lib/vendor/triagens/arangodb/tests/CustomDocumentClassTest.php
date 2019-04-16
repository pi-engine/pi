<?php
/**
 * ArangoDB PHP client testsuite
 * File: Database.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

/**
 * Class DatabaseTest
 * Basic Tests for the Database API implementation
 *
 * @property Connection $connection
 *
 * @package ArangoDBClient
 */
class CustomDocumentClassTest extends
    \PHPUnit_Framework_TestCase
{

    protected static $testsTimestamp;

    protected $collection;
    protected $collectionHandler;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        static::$testsTimestamp = str_replace('.', '_', (string) microtime(true));
    }


    public function setUp()
    {
        $this->connection        = getConnection();
        $this->collectionHandler = new CollectionHandler($this->connection);

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01');
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        $this->collection = new Collection();
        $this->collection->setName('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        $this->collectionHandler->create($this->collection);
    }


    /**
     * Try to retrieve a document with custom document class
     */
    public function testGetCustomDocumentWithHandler()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $document        = new Document();
        $documentHandler = new DocumentHandler($connection);

        $document->someAttribute = 'someValue';

        $documentId = $documentHandler->save($collection->getName(), $document);

        $documentHandler->setDocumentClass(CustomDocumentClass1::class);
        $resultingDocument1 = $documentHandler->get($collection->getName(), $documentId);
        static::assertInstanceOf(CustomDocumentClass1::class, $resultingDocument1, 'Retrieved document isn\'t made with provided CustomDocumentClass1!');

        $documentHandler->setDocumentClass(CustomDocumentClass2::class);
        $resultingDocument2 = $documentHandler->get($collection->getName(), $documentId);
        static::assertInstanceOf(CustomDocumentClass2::class, $resultingDocument2, 'Retrieved document isn\'t made with provided CustomDocumentClass2!');

        $documentHandler->setDocumentClass(Document::class);
        $resultingDocument = $documentHandler->get($collection->getName(), $documentId);
        static::assertInstanceOf(Document::class, $resultingDocument, 'Retrieved document isn\'t made with provided Document!');
        static::assertNotInstanceOf(CustomDocumentClass1::class, $resultingDocument, 'Retrieved document is made with CustomDocumentClass1!');
        static::assertNotInstanceOf(CustomDocumentClass2::class, $resultingDocument, 'Retrieved document is made with CustomDocumentClass2!');

        $resultingAttribute = $resultingDocument->someAttribute;
        static::assertSame('someValue', $resultingAttribute, 'Resulting Attribute should be "someValue". It\'s :' . $resultingAttribute);

        $resultingAttribute1 = $resultingDocument1->someAttribute;
        static::assertSame('someValue', $resultingAttribute1, 'Resulting Attribute should be "someValue". It\'s :' . $resultingAttribute);

        $resultingAttribute2 = $resultingDocument2->someAttribute;
        static::assertSame('someValue', $resultingAttribute2, 'Resulting Attribute should be "someValue". It\'s :' . $resultingAttribute);

        $documentHandler->remove($document);
    }

    /**
     * Try to retrieve a custom document class via Statement.
     */
    public function testGetCustomDocumentWithStatement()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $document        = new Document();
        $documentHandler = new DocumentHandler($connection);

        $document->someAttribute = 'anotherValue';

        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 1000,
                '_sanitize' => true,
            ]
        );
        $statement->setDocumentClass(CustomDocumentClass1::class);
        $statement->setQuery(sprintf('FOR a IN `%s` RETURN a', $collection->getName()));
        $cursor = $statement->execute();

        $result = $cursor->current();

        static::assertInstanceOf(CustomDocumentClass1::class, $result, 'Retrieved document isn\'t made with provided CustomDocumentClass1!');
        static::assertSame('anotherValue', $result->someAttribute, 'Expected value anotherValue, found :' . $result->someAttribute);

        $documentHandler->remove($document);
    }

    /**
     * Try to retrieve a custom document class via Export.
     */
    public function testGetCustomDocumentWithExport()
    {
        if (isCluster($this->connection)) {
            $this->markTestSkipped("test is only meaningful in single server");
        }

        $connection      = $this->connection;
        $collection      = $this->collection;
        $document        = new Document();
        $documentHandler = new DocumentHandler($connection);

        $document->someAttribute = 'exportValue';

        $documentHandler->save($collection->getName(), $document);

        $export = new Export($connection, $collection->getName(), [
            'batchSize' => 5000,
            '_flat'     => false,
            'flush'     => true,
        ]);

        // execute the export. this will return a special, forward-only cursor
        $export->setDocumentClass(CustomDocumentClass1::class);
        $cursor = $export->execute();

        $found = false;
        while ($docs = $cursor->getNextBatch()) {
            $found = true;
            static::assertTrue(count($docs) > 0, 'No documents retrieved!');
            foreach ($docs as $doc) {
                static::assertInstanceOf(CustomDocumentClass1::class, $doc, 'Retrieved document isn\'t made with provided CustomDocumentClass1!');
                static::assertSame('exportValue', $doc->someAttribute, 'Expected value exportValue, found :' . $doc->someAttribute);
            }
        }

        static::assertTrue($found, 'No batch results in Export');

        $documentHandler->remove($document);
    }

    public function testGetCustomDocumentWithBatch()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $documentHandler = new DocumentHandler($connection);
        $document1       = Document::createFromArray(
            ['someAttribute' => 'someValue', 'someOtherAttribute' => 'someOtherValue']
        );
        $docId1          = $documentHandler->save($this->collection->getName(), $document1);
        $document2       = Document::createFromArray(
            ['someAttribute' => 'someValue2', 'someOtherAttribute' => 'someOtherValue2']
        );
        $docId2          = $documentHandler->save($this->collection->getName(), $document2);

        $batch = new Batch($connection);
        $batch->setDocumentClass(CustomDocumentClass1::class);
        $batch->startCapture();

        $documentHandler->getById($this->collection->getName(), $docId1);
        $documentHandler->getById($this->collection->getName(), $docId2);

        $batch->process();
        $result = $batch->getPart(0)->getProcessedResponse();

        static::assertInstanceOf(CustomDocumentClass1::class, $result, 'Retrieved document isn\'t made with provided CustomDocumentClass1!');
        static::assertSame('someValue', $result->someAttribute, 'Expected value someValue, found :' . $result->someAttribute);

        $batchPart = $batch->getPart(1);
        $batchPart->setDocumentClass(CustomDocumentClass2::class);
        $result = $batchPart->getProcessedResponse();

        static::assertInstanceOf(CustomDocumentClass2::class, $result, 'Retrieved document isn\'t made with provided CustomDocumentClass2!');
        static::assertSame('someValue2', $result->someAttribute, 'Expected value someValue2, found :' . $result->someAttribute);

        $documentHandler->remove($document1);
        $documentHandler->remove($document2);
    }


    public function tearDown()
    {
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        unset($this->documentHandler, $this->document, $this->collectionHandler, $this->collection, $this->connection);
    }


}

/**
 * Class CustomCollectionHandler
 *
 * @package ArangoDBClient
 */
class CustomCollectionHandler extends CollectionHandler
{

}

/**
 * Class CustomDocumentClass1 & CustomDocumentClass2
 *
 * @package ArangoDBClient
 */
class CustomDocumentClass1 extends Document
{
}

class CustomDocumentClass2 extends Document
{
}

