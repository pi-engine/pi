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
class DatabaseTest extends
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
        $this->connection = getConnection();

        // remove existing databases to make test repeatable
        $databases = ['ArangoTestSuiteDatabaseTest01' . '_' . static::$testsTimestamp, 'ArangoTestSuiteDatabaseTest02' . '_' . static::$testsTimestamp];
        foreach ($databases as $database) {

            try {
                Database::delete($this->connection, $database);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Test if Databases can be created and deleted
     */
    public function testCreateDatabaseDeleteIt()
    {

        $database = 'ArangoTestSuiteDatabaseTest01' . '_' . static::$testsTimestamp;

        try {
            $e = null;
            Database::delete($this->connection, $database);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }

        $response = Database::create($this->connection, $database);

        static::assertEquals(
            false,
            $response['error'],
            'result[\'error\'] Did not return false, instead returned: ' . print_r($response, 1)
        );

        $response = Database::delete($this->connection, $database);

        static::assertEquals(
            false,
            $response['error'],
            'result[\'error\'] Did not return false, instead returned: ' . print_r($response, 1)
        );

        $response = Database::listDatabases($this->connection);
        static::assertArrayNotHasKey($database, array_flip($response['result']));
    }


    /**
     * Test if Databases can be created, if they can be listed, if they can be listed for the current user and deleted again
     */
    public function testCreateDatabaseGetListOfDatabasesAndDeleteItAgain()
    {

        $database = 'ArangoTestSuiteDatabaseTest01' . '_' . static::$testsTimestamp;

        $response = Database::create($this->connection, $database);

        static::assertEquals(
            false,
            $response['error'],
            'result[\'error\'] Did not return false, instead returned: ' . print_r($response, 1)
        );


        $response = Database::databases($this->connection);

        static::assertArrayHasKey($database, array_flip($response['result']));

        $responseUser = Database::listUserDatabases($this->connection);

        static::assertArrayHasKey($database, array_flip($responseUser['result']));


        $response = Database::delete($this->connection, $database);

        static::assertEquals(
            false,
            $response['error'],
            'result[\'error\'] Did not return false, instead returned: ' . print_r($response, 1)
        );
    }


    /**
     * Test if Databases can be created, if they are listed and deleted again
     */
    public function testCreateDatabaseGetInfoOfDatabasesAndDeleteItAgain()
    {

        $database = 'ArangoTestSuiteDatabaseTest01' . '_' . static::$testsTimestamp;

        $response = Database::create($this->connection, $database);

        static::assertEquals(
            false,
            $response['error'],
            'result[\'error\'] Did not return false, instead returned: ' . print_r($response, 1)
        );

        $this->connection->setDatabase($database);

        $response = Database::getInfo($this->connection);

        static::assertEquals(
            $database,
            $response['result']['name']
        );


        $this->connection->setDatabase('_system');

        $response = Database::getInfo($this->connection);
        static::assertEquals(
            '_system',
            $response['result']['name']
        );

        $response = Database::delete($this->connection, $database);

        static::assertEquals(
            false, $response['error'], 'result[\'error\'] Did not return false, instead returned: ' . print_r($response, 1)
        );
    }


    /**
     * Test if non-existent Databases can be deleted
     */
    public function testDeleteNonExistentDatabase()
    {

        $database = 'ArangoTestSuiteDatabaseTest01' . '_' . static::$testsTimestamp;


        // Try to get a non-existent document out of a nonexistent collection
        // This should cause an exception with a code of 404
        try {
            $e = null;
            Database::delete($this->connection, $database);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(
            404,
            $e->getCode(),
            'Should be 404, instead got: ' . $e->getCode()
        );
    }


    /**
     * Test if Databases can still be created, if current is not _system
     */
    public function testCreateDatabaseSwitchToItAndCreateAnotherOne()
    {

        $database  = 'ArangoTestSuiteDatabaseTest01' . '_' . static::$testsTimestamp;
        $database2 = 'ArangoTestSuiteDatabaseTest02' . '_' . static::$testsTimestamp;

        try {
            $e = null;
            Database::delete($this->connection, $database);
        } catch (\Exception $e) {
            // don't bother us... 
        }

        $response = Database::create($this->connection, $database);

        static::assertEquals(
            false,
            $response['error'],
            'result[\'error\'] Did not return false, instead returned: ' . print_r($response, 1)
        );


        // Try to create a database from within a non_system database

        $this->connection->setDatabase($database);

        $response = Database::getInfo($this->connection);
        static::assertEquals(
            $database,
            $response['result']['name']
        );

        try {
            $e = null;
            Database::create($this->connection, $database2);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(
            403,
            $e->getCode(),
            'Should be 403, instead got: ' . $e->getCode()
        );


        $this->connection->setDatabase('_system');

        $response = Database::getInfo($this->connection);
        static::assertEquals('_system', $response['result']['name']);

        $response = Database::delete($this->connection, $database);

        static::assertEquals(
            false,
            $response['error'],
            'result[\'error\'] Did not return false, instead returned: ' . print_r($response, 1)
        );
    }

    public function tearDown()
    {
        // clean up
        $databases = ['ArangoTestSuiteDatabaseTest01' . '_' . static::$testsTimestamp, 'ArangoTestSuiteDatabaseTest02' . '_' . static::$testsTimestamp];
        foreach ($databases as $database) {

            try {
                Database::delete($this->connection, $database);
            } catch (Exception $e) {
            }
        }

        unset($this->connection);
    }
}
