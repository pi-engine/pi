<?php
/**
 * ArangoDB PHP client testsuite
 * File: QueryTest.php
 *
 * @package ArangoDBClient
 * @author  Jan Steemann
 */

namespace ArangoDBClient;

/**
 * Class QueryTest
 *
 * @property Connection   $connection
 * @property QueryHandler $queryHandler
 *
 * @package ArangoDBClient
 */
class QueryTest extends
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
        $this->connection   = getConnection();
        $this->queryHandler = new QueryHandler($this->connection);

        $this->queryHandler->clearSlow();
    }

    /**
     * Test current query
     */
    public function testCurrentAndKill()
    {
        try {
            $result = $this->connection->post('/_admin/execute', 'return 1');
        } catch (\Exception $e) {
            // /_admin/execute API disabled on the server. must turn on
            // --javascript.allow-admin-execute on the server for this to work
            $this->markTestSkipped("need to start the server with --javascript.allow-admin-execute true to run this test");
            return;
        }

        $query   = 'RETURN SLEEP(30)';
        $command = 'require("internal").db._query("' . $query . '");';

        // executes the command on the server
        $this->connection->post('/_admin/execute', $command, ['X-Arango-Async' => 'true']);

        // sleep a bit because we do not know when the server will start executing the query
        sleep(3);

        $found = 0;
        foreach ($this->queryHandler->getCurrent() as $q) {
            static::assertFalse($q['stream']);
            if ($q['query'] === $query) {
                ++$found;
                $id = $q['id'];
            }
        }
        static::assertGreaterThanOrEqual(1, $found);

        // now kill the query
        $result = $this->queryHandler->kill($id);
        static::assertTrue($result);
    }

    /**
     * Test slow query - empty
     */
    public function testGetSlowEmpty()
    {
        static::assertEquals([], $this->queryHandler->getSlow());
    }

    /**
     * Test slow query - should contain one query
     */
    public function testGetSlow()
    {
        $query = 'RETURN SLEEP(10)';

        $statement = new Statement($this->connection, ['query' => $query]);
        $statement->execute();

        $found = 0;
        foreach ($this->queryHandler->getSlow() as $q) {
            static::assertFalse($q['stream']);
            if ($q['query'] === $query) {
                ++$found;

                static::assertTrue($q['runTime'] >= 10);
            }
        }
        static::assertEquals(1, $found);

        // clear slow log and check that it is empty afterwards
        $this->queryHandler->clearSlow();

        $found = 0;
        foreach ($this->queryHandler->getSlow() as $q) {
            if ($q['query'] === $query) {
                ++$found;
            }
        }
        static::assertEquals(0, $found);
    }


    /**
     * Test getting correct Timeout Exception
     *
     * @expectedException \ArangoDBClient\ClientException
     */
    public function testTimeoutException()
    {
        $old = $this->connection->getOption(ConnectionOptions::OPTION_TIMEOUT);
        $this->connection->setOption(ConnectionOptions::OPTION_TIMEOUT, 10);
        $query = 'RETURN SLEEP(13)';

        $statement = new Statement($this->connection, ['query' => $query]);

        try {
            $statement->execute();
            $this->connection->setOption(ConnectionOptions::OPTION_TIMEOUT, $old);
        } catch (ClientException $exception) {
            $this->connection->setOption(ConnectionOptions::OPTION_TIMEOUT, $old);
            static::assertEquals($exception->getCode(), 408);
            throw $exception;
        }
        
    }

    public function tearDown()
    {
        unset($this->queryHandler, $this->connection);
    }
}
