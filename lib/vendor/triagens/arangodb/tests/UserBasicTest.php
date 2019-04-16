<?php
/**
 * ArangoDB PHP client testsuite
 * File: UserBasicTest.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

/**
 * Class UserBasicTest
 *
 * @property Connection  $connection
 * @property UserHandler userHandler
 *
 * @package ArangoDBClient
 */
class UserBasicTest extends
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
        $this->connection  = getConnection();
        $this->userHandler = new UserHandler($this->connection);

        try {
            $this->userHandler->removeUser('testUser1');
        } catch (\Exception $e) {
        }

        try {
            $this->userHandler->removeUser('testUser42');
        } catch (\Exception $e) {
        }
    }


    /**
     * Test database permission handling
     */
    public function testGrantPermissions()
    {
        if (!useAuthentication()) {
            $this->markTestSkipped("test is only meaningful with authentication enabled");
        }

        $result = $this->userHandler->addUser('testUser42', 'testPasswd', true);
        static::assertTrue($result);

        $result = $this->userHandler->grantPermissions('testUser42', $this->connection->getDatabase());
        static::assertTrue($result);

        $options                                        = $this->connection->getOptions()->getAll();
        $options[ConnectionOptions::OPTION_AUTH_USER]   = 'testUser42';
        $options[ConnectionOptions::OPTION_AUTH_PASSWD] = 'testPasswd';
        $userConnection                                 = new Connection($options);

        $userHandler = new UserHandler($userConnection);
        $result      = $userHandler->getDatabases('testUser42');
        static::assertEquals(['_system' => 'rw'], $result);


        $this->userHandler->removeUser('testUser42');

        $e = null;
        try {
            $userHandler->getDatabases('testUser42');
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertEquals(401, $e->getCode());
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');
    }

    /**
     * Test database permission handling
     */
    public function testGrantAndRevokePermissions()
    {
        if (!useAuthentication()) {
            $this->markTestSkipped("test is only meaningful with authentication enabled");
        }

        $result = $this->userHandler->addUser('testUser42', 'testPasswd', true);
        static::assertTrue($result);

        $result = $this->userHandler->grantPermissions('testUser42', $this->connection->getDatabase());
        static::assertTrue($result);

        $options                                        = $this->connection->getOptions()->getAll();
        $options[ConnectionOptions::OPTION_AUTH_USER]   = 'testUser42';
        $options[ConnectionOptions::OPTION_AUTH_PASSWD] = 'testPasswd';
        $userConnection                                 = new Connection($options);

        $userHandler = new UserHandler($userConnection);
        $result      = $userHandler->getDatabases('testUser42');
        static::assertEquals(['_system' => 'rw'], $result);

        $result = $this->userHandler->revokePermissions('testUser42', $this->connection->getDatabase());
        static::assertTrue($result);

        $result = $userHandler->getDatabases('testUser42');
        // never versions of ArangoDB do not return "none" for 
        // databases for which there are no permissions
        if (!empty($result)) {
            static::assertEquals(['_system' => 'none'], $result);
        }
    }


    /**
     * Test database permission handling
     */
    public function testGrantDatabasePermissions()
    {
        if (!useAuthentication()) {
            $this->markTestSkipped("test is only meaningful with authentication enabled");
        }

        $result = $this->userHandler->addUser('testUser42', 'testPasswd', true);
        static::assertTrue($result);

        $result = $this->userHandler->grantDatabasePermissions('testUser42', $this->connection->getDatabase());
        static::assertTrue($result);

        $options                                        = $this->connection->getOptions()->getAll();
        $options[ConnectionOptions::OPTION_AUTH_USER]   = 'testUser42';
        $options[ConnectionOptions::OPTION_AUTH_PASSWD] = 'testPasswd';
        $userConnection                                 = new Connection($options);

        $userHandler = new UserHandler($userConnection);
        $result      = $userHandler->getDatabases('testUser42');
        static::assertEquals(['_system' => 'rw'], $result);

        $result = $userHandler->getDatabasePermissionLevel('testUser42', '_system');
        static::assertEquals('rw', $result);


        $result = $this->userHandler->grantDatabasePermissions('testUser42', $this->connection->getDatabase(), 'ro');
        static::assertTrue($result);

        $options                                        = $this->connection->getOptions()->getAll();
        $options[ConnectionOptions::OPTION_AUTH_USER]   = 'testUser42';
        $options[ConnectionOptions::OPTION_AUTH_PASSWD] = 'testPasswd';
        $userConnection                                 = new Connection($options);

        $userHandler = new UserHandler($userConnection);
        $result      = $userHandler->getDatabases('testUser42');
        static::assertEquals(['_system' => 'ro'], $result);

        $result = $userHandler->getDatabasePermissionLevel('testUser42', '_system');
        static::assertEquals('ro', $result);


        $this->userHandler->removeUser('testUser42');

        $e = null;
        try {
            $userHandler->getDatabases('testUser42');
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertEquals(401, $e->getCode());
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');
    }

    /**
     * Test database permission handling
     */
    public function testGrantAndRevokeDatabasePermissions()
    {
        if (!useAuthentication()) {
            $this->markTestSkipped("test is only meaningful with authentication enabled");
        }

        $result = $this->userHandler->addUser('testUser42', 'testPasswd', true);
        static::assertTrue($result);

        $result = $this->userHandler->grantDatabasePermissions('testUser42', $this->connection->getDatabase());
        static::assertTrue($result);

        $options                                        = $this->connection->getOptions()->getAll();
        $options[ConnectionOptions::OPTION_AUTH_USER]   = 'testUser42';
        $options[ConnectionOptions::OPTION_AUTH_PASSWD] = 'testPasswd';
        $userConnection                                 = new Connection($options);

        $userHandler = new UserHandler($userConnection);
        $result      = $userHandler->getDatabases('testUser42');
        static::assertEquals(['_system' => 'rw'], $result);

        $result = $userHandler->getDatabasePermissionLevel('testUser42', '_system');
        static::assertEquals('rw', $result);


        $result = $this->userHandler->revokeDatabasePermissions('testUser42', $this->connection->getDatabase());
        static::assertTrue($result);

        $result = $userHandler->getDatabases('testUser42');
        // never versions of ArangoDB do not return "none" for
        // databases for which there are no permissions
        if (!empty($result)) {
            static::assertEquals(['_system' => 'none'], $result);
        }

        $result = $userHandler->getDatabasePermissionLevel('testUser42', '_system');
        static::assertEquals('none', $result);
    }


    /**
     * Test collection permission handling
     */
    public function testGrantCollectionPermissions()
    {
        if (!useAuthentication()) {
            $this->markTestSkipped("test is only meaningful with authentication enabled");
        }

        $result = $this->userHandler->addUser('testUser42', 'testPasswd', true);
        static::assertTrue($result);

        $collectionHandler = new CollectionHandler($this->connection);
        $collectionName    = 'PermissionTestCollection'. '_' . static::$testsTimestamp;
        $collectionHandler->create($collectionName);

        $result = $this->userHandler->grantCollectionPermissions('testUser42', $this->connection->getDatabase(), $collectionName);
        static::assertTrue($result);

        $options                                        = $this->connection->getOptions()->getAll();
        $options[ConnectionOptions::OPTION_AUTH_USER]   = 'testUser42';
        $options[ConnectionOptions::OPTION_AUTH_PASSWD] = 'testPasswd';
        $userConnection                                 = new Connection($options);

        $userHandler = new UserHandler($userConnection);

        $result = $userHandler->getCollectionPermissionLevel('testUser42', '_system', $collectionName);
        static::assertEquals('rw', $result);

        $result = $this->userHandler->grantCollectionPermissions('testUser42', $this->connection->getDatabase(), $collectionName, 'ro');
        static::assertTrue($result);

        $options                                        = $this->connection->getOptions()->getAll();
        $options[ConnectionOptions::OPTION_AUTH_USER]   = 'testUser42';
        $options[ConnectionOptions::OPTION_AUTH_PASSWD] = 'testPasswd';
        $userConnection                                 = new Connection($options);

        $userHandler = new UserHandler($userConnection);
        $result      = $userHandler->getCollectionPermissionLevel('testUser42', '_system', $collectionName);
        static::assertEquals('ro', $result);


        $this->userHandler->removeUser('testUser42');
        $e = null;
        try {
            $userHandler->getCollectionPermissionLevel('testUser42', '_system', $collectionName);
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertEquals(401, $e->getCode(), 'Should get 401, instead got: ' . $e->getCode());
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');
    }

    /**
     * Test collection permission handling
     */
    public function testGrantAndRevokeCollectionPermissions()
    {
        if (!useAuthentication()) {
            $this->markTestSkipped("test is only meaningful with authentication enabled");
        }

        $result = $this->userHandler->addUser('testUser42', 'testPasswd', true);
        static::assertTrue($result);

        $collectionName    = 'PermissionTestCollection'. '_' . static::$testsTimestamp;
        $result         = $this->userHandler->grantCollectionPermissions('testUser42', $this->connection->getDatabase(), $collectionName);
        static::assertTrue($result);

        $options                                        = $this->connection->getOptions()->getAll();
        $options[ConnectionOptions::OPTION_AUTH_USER]   = 'testUser42';
        $options[ConnectionOptions::OPTION_AUTH_PASSWD] = 'testPasswd';
        $userConnection                                 = new Connection($options);

        $userHandler = new UserHandler($userConnection);
        $result      = $userHandler->getCollectionPermissionLevel('testUser42', '_system', $collectionName);
        static::assertEquals('rw', $result);

        $result = $this->userHandler->revokeCollectionPermissions('testUser42', $this->connection->getDatabase(), $collectionName);
        static::assertTrue($result);

        $result = $userHandler->getCollectionPermissionLevel('testUser42', '_system', $collectionName);

        // newer versions of ArangoDB do not return "none" for
        // databases for which there are no permissions
        static::assertEquals('none', $result);
    }


    /**
     * Test if a user can be added, replaced, updated and removed
     */
    public function testAddReplaceUpdateGetAndDeleteUserWithNullValues()
    {
        $result = $this->userHandler->addUser('testUser1', null, null, null);
        static::assertTrue($result);


        $this->userHandler->replaceUser('testUser1', null, null, null);
        static::assertTrue($result);


        $this->userHandler->updateUser('testUser1', null, null, null);
        static::assertTrue($result);


        $this->userHandler->removeUser('testUser1');
        static::assertTrue($result);

        try {
            $this->userHandler->get('testUser1');
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertEquals(404, $e->getCode(), 'Should get 404, instead got: ' . $e->getCode());
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');
    }


    /**
     * Test if user can be added, modified and finally removed
     */
    public function testAddReplaceUpdateGetAndDeleteUserWithNonNullValues()
    {
        $result = $this->userHandler->addUser('testUser1', 'testPass1', true, ['level' => 1]);
        static::assertTrue($result);

        $e = null;
        try {
            $this->userHandler->addUser('testUser1', 'testPass1', true, ['level' => 1]);
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertTrue($e->getCode() === 400 || $e->getCode() === 409);
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');


        $response = $this->userHandler->get('testUser1');
        $extra    = $response->extra;
        static::assertTrue($response->active);
        static::assertEquals(1, $extra['level'], 'Should return 1');


        $this->userHandler->replaceUser('testUser1', 'testPass2', false, ['level' => 2]);
        static::assertTrue($result);


        $response = $this->userHandler->get('testUser1');
        $extra    = $response->extra;
        static::assertFalse($response->active);

        static::assertEquals(2, $extra['level'], 'Should return 2');


        $this->userHandler->updateUser('testUser1', null, null, ['level' => 3]);
        static::assertTrue($result);


        $response = $this->userHandler->get('testUser1');
        $extra    = $response->extra;
        static::assertFalse($response->active);

        static::assertEquals(3, $extra['level'], 'Should return 3');

        $this->userHandler->removeUser('testUser1');
        static::assertTrue($result);
    }


    // test functions on non-existent user
    public function testFunctionsOnNonExistentUser()
    {
        $e = null;
        try {
            $this->userHandler->removeUser('testUser1');
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertEquals(404, $e->getCode(), 'Should get 404, instead got: ' . $e->getCode());
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');


        $e = null;
        try {
            $this->userHandler->updateUser('testUser1', null, null, ['level' => 3]);
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertEquals(404, $e->getCode(), 'Should get 404, instead got: ' . $e->getCode());
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');


        $e = null;
        try {
            $this->userHandler->replaceUser('testUser1', 'testPass2', false, ['level' => 2]);
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertEquals(404, $e->getCode(), 'Should get 404, instead got: ' . $e->getCode());
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');


        $e = null;
        try {
            $this->userHandler->get('testUser1');
        } catch (\Exception $e) {
            // Just give us the $e
            static::assertEquals(404, $e->getCode(), 'Should get 404, instead got: ' . $e->getCode());
        }
        static::assertInstanceOf(ServerException::class, $e, 'should have gotten an exception');
    }

    public function tearDown()
    {
        try {
            $this->userHandler->removeUser('testUser1');
        } catch (\Exception $e) {
            // Do nothing
        }

        try {
            $this->userHandler->removeUser('testUser42');
        } catch (\Exception $e) {
            // Do nothing
        }

        unset($this->userHandler, $this->connection);
    }
}
