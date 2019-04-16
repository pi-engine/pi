<?php
/**
 * ArangoDB PHP client testsuite
 * File: QueryCacheTest.php
 *
 * @package ArangoDBClient
 * @author  Jan Steemann
 */

namespace ArangoDBClient;

/**
 * Class QueryCacheTest
 *
 * @property Connection   $connection
 * @property QueryHandler $queryHandler
 *
 * @package ArangoDBClient
 */
class QueryCacheTest extends
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
        $this->cacheHandler      = new QueryCacheHandler($this->connection);
        $this->collectionHandler = new CollectionHandler($this->connection);

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        $this->cacheHandler->disable();
    }

    private function setupCollection()
    {
        $name             = 'ArangoDB_PHP_TestSuite_TestCollection' . '_' . static::$testsTimestamp;
        $this->collection = new Collection($name);
        $this->collectionHandler->create($this->collection);

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery("FOR i IN 1..2000 INSERT { value: i, _key: CONCAT('test', i) } INTO " . $name);

        $statement->execute();
    }


    /**
     * Test clearing of query cache
     */
    public function testClear()
    {
        $this->setupCollection();

        $this->cacheHandler->enable();

        $query = 'FOR i IN ' . $this->collection->getName() . ' FILTER i.value >= 1998 SORT i.value RETURN i.value';

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $statement->execute();

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        if (!isCluster($this->connection)) {
            static::assertTrue($cursor->getCached()); // should be in cache now
        }

        // now clear the cache
        $this->cacheHandler->clear();

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // shouldn't be in cache because we cleared it

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        if (!isCluster($this->connection)) {
            static::assertTrue($cursor->getCached()); // should be in cache again
        }
    }
    
    /**
     * Test getting entries of query cache
     */
    public function testGetEntries()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in single server");
            return;
        }
        $this->setupCollection();

        $this->cacheHandler->enable();

        $query = 'FOR i IN ' . $this->collection->getName() . ' FILTER i.value >= 1998 SORT i.value RETURN i.value';

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $statement->execute();

        if (!isCluster($this->connection)) {
            $entries = $this->cacheHandler->getEntries();

            static::assertTrue(sizeof($entries) > 0);

            $found = false;
            foreach ($entries as $entry) {
                if ($entry['query'] === $query) {
                    $found = true;
                    break;
                }
            }

            static::assertTrue($found, "query not found in cache!");
        }
    }


    /**
     * Test enabled query cache
     */
    public function testEnable()
    {
        $this->setupCollection();

        $this->cacheHandler->enable();

        $query = 'FOR i IN ' . $this->collection->getName() . ' FILTER i.value >= 1998 SORT i.value RETURN i.value';

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // not in cache yet

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        if (!isCluster($this->connection)) {
            static::assertTrue($cursor->getCached()); // should be in cache now
        }
    }


    /**
     * Test enabled query cache
     */
    public function testEnabledButExplicitlyDisabledForQuery()
    {
        $this->setupCollection();

        $this->cacheHandler->enable();

        $query = 'FOR i IN ' . $this->collection->getName() . ' FILTER i.value >= 1998 SORT i.value RETURN i.value';

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $statement->execute();

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        if (!isCluster($this->connection)) {
            static::assertTrue($cursor->getCached()); // should be in cache now
        }

        // re-execute same query, but with cache disabled
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setCache(false);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached());
    }


    /**
     * Test disabled query cache
     */
    public function testDisable()
    {
        $this->setupCollection();

        $this->cacheHandler->disable();

        $query = 'FOR i IN ' . $this->collection->getName() . ' FILTER i.value >= 1998 SORT i.value RETURN i.value';

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // not in cache

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // still not in cache
    }


    /**
     * Test query cache demand mode
     */
    public function testDemandModeUsed1()
    {
        $this->setupCollection();

        $this->cacheHandler->enableDemandMode();

        $query = 'FOR i IN ' . $this->collection->getName() . ' FILTER i.value >= 1998 SORT i.value RETURN i.value';

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setCache(true);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // not in cache

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setCache(true);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        if (!isCluster($this->connection)) {
            static::assertTrue($cursor->getCached()); // now the query should be in the cache, because we set the cache attribute for the query
        }
    }


    /**
     * Test query cache demand mode
     */
    public function testDemandModeUsed2()
    {
        $this->setupCollection();

        $this->cacheHandler->enableDemandMode();

        $query = 'FOR i IN ' . $this->collection->getName() . ' FILTER i.value >= 1998 SORT i.value RETURN i.value';

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setCache(true);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // not in cache

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setCache(false);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // we said we don't want to use the cache

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setCache(true);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        if (!isCluster($this->connection)) {
            static::assertTrue($cursor->getCached()); // we said we want to use the cache
        }
    }


    /**
     * Test query cache demand mode
     */
    public function testDemandModeUnused()
    {
        $this->setupCollection();

        $this->cacheHandler->enableDemandMode();

        $query = 'FOR i IN ' . $this->collection->getName() . ' FILTER i.value >= 1998 SORT i.value RETURN i.value';

        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // not in cache

        // re-execute same query
        $statement = new Statement($this->connection, ['_flat' => true]);
        $statement->setQuery($query);
        $cursor = $statement->execute();

        static::assertEquals([1998, 1999, 2000], $cursor->getAll());
        static::assertFalse($cursor->getCached()); // still not in cache, because we didn't set cache attribute for query
    }


    public function tearDown()
    {
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        $this->cacheHandler->disable();

        unset($this->cacheHandler, $this->connection);
    }
}
