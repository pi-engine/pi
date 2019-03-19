<?php
/**
 * ArangoDB PHP client testsuite
 * File: StatementTest.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

function filtered(array $values)
{
    unset($values['executionTime']);
    unset($values['httpRequests']);
    unset($values['peakMemoryUsage']);

    return $values;
}


/**
 * Class StatementTest
 *
 * @property Connection        $connection
 * @property Collection        $collection
 * @property CollectionHandler $collectionHandler
 * @property DocumentHandler   $documentHandler
 *
 * @package ArangoDBClient
 */
class StatementTest extends
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

        // clean up first
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        $this->collection = new Collection();
        $this->collection->setName('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        $this->collectionHandler->create($this->collection);
    }


    /**
     * This is just a test to really test connectivity with the server before moving on to further tests.
     */
    public function testExecuteStatement()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $document        = new Document();
        $documentHandler = new DocumentHandler($connection);

        $document->someAttribute = 'someValue';

        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 1000,
                '_sanitize' => true,
            ]
        );
        $statement->setQuery('FOR a IN `ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp . '` RETURN a');
        $cursor = $statement->execute();

        $result = $cursor->current();

        static::assertSame(
            'someValue', $result->someAttribute, 'Expected value someValue, found :' . $result->someAttribute
        );
    }


    /**
     * Test warnings returned by statement
     */
    public function testStatementReturnNoWarnings()
    {
        $connection = $this->connection;

        $statement = new Statement($connection, ['query' => 'RETURN 1']);
        $cursor    = $statement->execute();

        static::assertCount(0, $cursor->getWarnings());
    }

    /**
     * Test warnings returned by statement
     */
    public function testStatementReturnWarnings()
    {
        $connection = $this->connection;

        $statement = new Statement($connection, ['query' => 'RETURN 1/0']);
        $cursor    = $statement->execute();

        static::assertCount(1, $cursor->getWarnings());
        $warnings = $cursor->getWarnings();
        static::assertEquals(1562, $warnings[0]['code']);
    }
    
    /**
     * Test fail if a warning occurs during querying
     */
    public function testStatementFailOnWarning()
    {
        $connection = $this->connection;

        $statement = new Statement($connection, ['query' => 'RETURN 1/0']);
        $statement->setFailOnWarning();

        try {
            $cursor = $statement->execute();
            static::assertTrue(false);
        } catch (ServerException $e) {
            static::assertEquals(1562, $e->getServerCode());
        }
    }
    
    /**
     * Test fail with memory limit hit
     */
    public function testStatementFailWithMemoryLimit()
    {
        $connection = $this->connection;

        $statement = new Statement($connection, ['query' => 'FOR i IN 1..100000 RETURN CONCAT("test", TO_NUMBER(i))']);
        $statement->setMemoryLimit(10000);

        try {
            $cursor = $statement->execute();
            static::assertTrue(false);
        } catch (ServerException $e) {
            static::assertEquals(32, $e->getServerCode());
        }
    }


    /**
     * Test statistics returned by query
     */
    public function testStatisticsInsert()
    {
        $connection = $this->connection;
        $collection = $this->collection;

        $statement = new Statement($connection, []);
        $statement->setQuery('FOR i IN 1..1000 INSERT { _key: CONCAT("test", i) } IN ' . $collection->getName());
        $cursor = $statement->execute();

        static::assertEquals(1000, $this->collectionHandler->count($collection->getName()));

        $extra = $cursor->getExtra();
        static::assertEquals([], $extra['warnings']);

        static::assertEquals(
            [
                'writesExecuted' => 1000,
                'writesIgnored'  => 0,
                'scannedFull'    => 0,
                'scannedIndex'   => 0,
                'filtered'       => 0
            ], filtered($extra['stats'])
        );

        static::assertEquals(1000, $cursor->getWritesExecuted());
        static::assertEquals(0, $cursor->getWritesIgnored());
        static::assertEquals(0, $cursor->getScannedFull());
        static::assertEquals(0, $cursor->getScannedIndex());
        static::assertEquals(0, $cursor->getFiltered());
    }

    /**
     * Test statistics returned by query
     */
    public function testStatisticsSelectRemove()
    {
        $connection = $this->connection;
        $collection = $this->collection;

        $statement = new Statement($connection, []);
        $statement->setQuery('FOR i IN 1..1000 INSERT { _key: CONCAT("test", i) } IN ' . $collection->getName());
        $statement->execute();

        $statement = new Statement($connection, []);
        $statement->setQuery('FOR i IN ' . $collection->getName() . ' FILTER i._key IN [ "test1", "test35", "test99" ] REMOVE i IN ' . $collection->getName());
        $cursor = $statement->execute();

        static::assertEquals(997, $this->collectionHandler->count($collection->getName()));

        $extra = $cursor->getExtra();
        static::assertEquals([], $extra['warnings']);

        static::assertEquals(
            [
                'writesExecuted' => 3,
                'writesIgnored'  => 0,
                'scannedFull'    => 0,
                'scannedIndex'   => 3,
                'filtered'       => 0
            ], filtered($extra['stats'])
        );

        static::assertEquals(3, $cursor->getWritesExecuted());
        static::assertEquals(0, $cursor->getWritesIgnored());
        static::assertEquals(0, $cursor->getScannedFull());
        static::assertEquals(3, $cursor->getScannedIndex());
        static::assertEquals(0, $cursor->getFiltered());
    }

    /**
     * Test statistics returned by query
     */
    public function testStatisticsSelect()
    {
        $connection = $this->connection;
        $collection = $this->collection;

        $statement = new Statement($connection, []);
        $statement->setQuery('FOR i IN 1..1000 INSERT { _key: CONCAT("test", i), value: i } IN ' . $collection->getName());
        $statement->execute();

        $statement = new Statement($connection, []);
        $statement->setQuery('FOR i IN ' . $collection->getName() . ' FILTER i.value <= 500 RETURN i');
        $cursor = $statement->execute();

        static::assertEquals(1000, $this->collectionHandler->count($collection->getName()));

        $extra = $cursor->getExtra();
        static::assertEquals([], $extra['warnings']);

        static::assertEquals(
            [
                'writesExecuted' => 0,
                'writesIgnored'  => 0,
                'scannedFull'    => 1000,
                'scannedIndex'   => 0,
                'filtered'       => 500
            ], filtered($extra['stats'])
        );

        static::assertEquals(0, $cursor->getWritesExecuted());
        static::assertEquals(0, $cursor->getWritesIgnored());
        static::assertEquals(1000, $cursor->getScannedFull());
        static::assertEquals(0, $cursor->getScannedIndex());
        static::assertEquals(500, $cursor->getFiltered());
    }

    /**
     * This is just a test to really test connectivity with the server before moving on to further tests.
     * We expect an exception here:
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testExecuteStatementWithWrongEncoding()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $document        = new Document();
        $documentHandler = new DocumentHandler($connection);

        $document->someAttribute = 'someValue';

        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 1000,
                '_sanitize' => true,
            ]
        );
        // inject wrong encoding
        $isoValue = iconv(
            'UTF-8',
            'ISO-8859-1//TRANSLIT',
            '\'FOR ü IN `ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp . '` RETURN ü'
        );

        $statement->setQuery($isoValue);
        $cursor = $statement->execute();

        $result = $cursor->current();

        static::assertSame(
            'someValue', $result->someAttribute, 'Expected value someValue, found :' . $result->someAttribute
        );
    }


    /**
     * Test if the explain function works
     */
    public function testExplainStatement()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $document        = new Document();
        $documentHandler = new DocumentHandler($connection);

        $document->someAttribute = 'someValue';

        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 1000,
                '_sanitize' => true,
            ]
        );
        $statement->setQuery('FOR a IN `ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp . '` RETURN a');
        $result = $statement->explain();

        static::assertArrayHasKey('plan', $result, 'result-array does not contain plan !');
    }


    /**
     * Test if the validate function works
     */
    public function testValidateStatement()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $document        = new Document();
        $documentHandler = new DocumentHandler($connection);

        $document->someAttribute = 'someValue';

        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => '',
                'count'     => true,
                'batchSize' => 1000,
                '_sanitize' => true,
            ]
        );
        $statement->setQuery('FOR a IN `ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp . '` RETURN a');
        $result = $statement->validate();
        static::assertArrayHasKey('bindVars', $result, 'result-array does not contain plan !');
    }

    /**
     * Execute a statement that does not produce documents
     */
    public function testExecuteStatementFlat()
    {
        $connection = $this->connection;

        $statement = new Statement(
            $connection, [
                'query'     => 'RETURN SORTED_UNIQUE([ 1, 1, 2 ])',
                'count'     => true,
                '_sanitize' => true,
                '_flat'     => true
            ]
        );
        $cursor    = $statement->execute();
        static::assertCount(0, $cursor->getWarnings());
        static::assertEquals(
            [[1, 2]],
            $cursor->getAll()
        );
    }

    public function testStatementThatReturnsScalarResponses()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $document        = new Document();
        $documentHandler = new DocumentHandler($connection);

        $document->name = 'john';

        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => 'FOR a IN `ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp . '` RETURN a.name',
                'count'     => true,
                '_sanitize' => true
            ]
        );

        $cursor = $statement->execute();

        static::assertCount(0, $cursor->getWarnings());

        foreach ($cursor->getAll() as $row) {
            static::assertNotInstanceOf(Document::class, $row, 'A document object was in the result set!');
        }
    }

    public function testStatementWithFullCount()
    {
        $connection = $this->connection;
        $collection = $this->collection;

        $documentHandler = new DocumentHandler($connection);

        $document       = new Document();
        $document->name = 'john';
        $documentHandler->save($collection->getName(), $document);

        $document       = new Document();
        $document->name = 'peter';
        $documentHandler->save($collection->getName(), $document);

        $document       = new Document();
        $document->name = 'jane';
        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => 'FOR a IN `ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp . '` LIMIT 2 RETURN a.name',
                'count'     => true,
                'fullCount' => true,
                '_sanitize' => true
            ]
        );

        static::assertTrue($statement->getCount());
        static::assertTrue($statement->getFullcount());
        $cursor = $statement->execute();

        static::assertCount(0, $cursor->getWarnings());
        static::assertEquals(2, $cursor->getCount(), 'The number of results in the cursor should be 2');
        static::assertEquals(3, $cursor->getFullCount(), 'The fullCount should be 3');
    }
    
    public function testTtl()
    {
        $connection = $this->connection;
        $collection = $this->collection;

        $statement = new Statement(
            $connection, [
                'query'     => 'FOR i IN 1..1000 INSERT { value: i } IN ' . $collection->getName()
            ]
        );
        static::assertNull($statement->getTtl());

        $cursor = $statement->execute();

        $statement = new Statement(
            $connection, [
                'query'     => 'FOR doc IN ' . $collection->getName() . ' RETURN doc',
                'ttl'       => 1,
                'count'     => true,
                'batchSize' => 100
            ]
        );

        static::assertEquals(1, $statement->getTtl());
        static::assertEquals(100, $statement->getBatchSize());
        static::assertTrue($statement->getCount());

        $cursor = $statement->execute();
        static::assertEquals(1000, $cursor->getCount());

        // let the cursor time out
        sleep(8);
        
        $excepted = false;
        try {
            $all = $cursor->getAll();
            static::assertEquals(1000, count($all));
        } catch (\Exception $e) {
            $excepted = true;
        }

        static::assertTrue($excepted);
    }
    
    public function testStatementStreaming()
    {
        $connection = $this->connection;
        $collection = $this->collection;

        $statement = new Statement(
            $connection, [
                'query'     => 'FOR i IN 1..5000 INSERT { value: i } IN ' . $collection->getName()
            ]
        );
        static::assertNull($statement->getStream());

        $cursor = $statement->execute();

        $statement = new Statement(
            $connection, [
                'query'     => 'FOR doc IN ' . $collection->getName() . ' RETURN doc',
                'count'     => false,
                'stream'    => true
            ]
        );

        static::assertTrue($statement->getStream());

        $cursor = $statement->execute();
        static::assertEquals(5000, count($cursor->getAll()));
        static::assertEquals(5000, $cursor->getCount());
    }

    public function testBindReservedValue()
    {
        $connection = $this->connection;
        $collection = $this->collection;

        $documentHandler = new DocumentHandler($connection);

        $document       = new Document();
        $document->file = 'testFooBar';
        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => 'FOR a IN `ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp . '` FILTER a.file == @file RETURN a.file',
                'bindVars'  => ['file' => 'testFooBar'],
                '_sanitize' => true
            ]
        );

        $cursor = $statement->execute();

        $rows = $cursor->getAll();
        static::assertEquals('testFooBar', $rows[0]);
    }


    public function testBindReservedName()
    {
        $connection = $this->connection;
        $collection = $this->collection;

        $documentHandler = new DocumentHandler($connection);

        $document       = new Document();
        $document->test = 'file';
        $documentHandler->save($collection->getName(), $document);

        $statement = new Statement(
            $connection, [
                'query'     => 'FOR a IN `ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp . '` FILTER a.test == @test RETURN a.test',
                'bindVars'  => ['test' => 'file'],
                '_sanitize' => true
            ]
        );

        $cursor = $statement->execute();

        $rows = $cursor->getAll();
        static::assertEquals('file', $rows[0]);
    }


    /**
     * Test cache attribute
     */
    public function testCacheAttributeTrue()
    {
        $statement = new Statement($this->connection, ['cache' => true, '_flat' => true]);
        $statement->setQuery('FOR i IN 1..100 RETURN i');

        static::assertTrue($statement->getCache());
    }


    /**
     * Test cache attribute
     */
    public function testCacheAttributeFalse()
    {
        $statement = new Statement($this->connection, ['cache' => false, '_flat' => true]);
        $statement->setQuery('FOR i IN 1..100 RETURN i');

        static::assertFalse($statement->getCache());
    }


    /**
     * Test cache attribute
     */
    public function testCacheAttributeNull()
    {
        $statement = new Statement($this->connection, ['cache' => null, '_flat' => true]);
        $statement->setQuery('FOR i IN 1..100 RETURN i');

        static::assertNull($statement->getCache());
    }


    /**
     * Test cache attribute
     */
    public function testCacheAttributeNotSet()
    {
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery('FOR i IN 1..100 RETURN i');

        static::assertNull($statement->getCache());
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
