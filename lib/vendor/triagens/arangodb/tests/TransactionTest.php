<?php
/**
 * ArangoDB PHP client testsuite
 * File: TransactionTest.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

/**
 * Class TransactionTest
 *
 * Basic Tests for the Transaction API implementation
 *
 * @property Connection        $connection
 * @property CollectionHandler $collectionHandler
 * @property Collection        $collection1
 * @property Collection        $collection2
 * @package ArangoDBClient
 */
class TransactionTest extends
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

        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_02' . '_' . static::$testsTimestamp);
        } catch (Exception $e) {
            //Silence the exception
        }


        $this->collection1 = new Collection();
        $this->collection1->setName('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        $this->collectionHandler->create($this->collection1);

        $this->collection2 = new Collection();
        $this->collection2->setName('ArangoDB_PHP_TestSuite_TestCollection_02' . '_' . static::$testsTimestamp);
        $this->collectionHandler->create($this->collection2);
        
        $adminHandler = new AdminHandler($this->connection);
        $this->isMMFilesEngine         = ($adminHandler->getEngine()["name"] == "mmfiles"); 
    }

    /**
     * Test if a deadlock occurs and error 29 is thrown
     */
    public function testDeadlockHandling()
    {
        if (!$this->isMMFilesEngine) {
            $this->markTestSkipped("test is only meaningful with the mmfiles engine");
        }

        try {
            $result = $this->connection->post('/_admin/execute', 'return 1');
        } catch (\Exception $e) {
            // /_admin/execute API disabled on the server. must turn on
            // --javascript.allow-admin-execute on the server for this to work
            $this->markTestSkipped("need to start the server with --javascript.allow-admin-execute true to run this test");
            return;
        }

        $w1      = [$this->collection1->getName()];
        $action1 = '
        try {
          require("internal").db._executeTransaction({ collections: { write: [ "' . $this->collection2->getName() . '" ] }, action: function () {
          require("internal").wait(7, false);
          var db = require("internal").db;
          db.' . $this->collection1->getName() . '.any();
          }});
          return { message: "ok" };
        } catch (err) {
          return { message: err.errorNum };
        }
        ';

        $result1 = $this->connection->post('/_admin/execute?returnAsJSON=true', $action1, ['X-Arango-Async' => 'store']);
        $id1     = $result1->getHeader('x-arango-async-id');

        $action2 = '
        try {
          require("internal").db._executeTransaction({ collections: { write: [ "' . $this->collection1->getName() . '" ] }, action: function () {
            require("internal").wait(7, false);
            var db = require("internal").db;
            db.' . $this->collection2->getName() . '.any();
          }});
          return { message: "ok" };
        } catch (err) {
          return { message: err.errorNum };
        }
        ';

        $result2 = $this->connection->post('/_admin/execute?returnAsJSON=true', $action2, ['X-Arango-Async' => 'store']);
        $id2     = $result2->getHeader('x-arango-async-id');

        $tries   = 0;
        $got1    = false;
        $got2    = false;
        $result1 = null;
        $result2 = null;
        while ($tries++ < 20) {
            if (!$got1) {
                try {
                    $result1 = $this->connection->put('/_api/job/' . $id1, '');
                    if ($result1->getHeader('x-arango-async-id') !== null) {
                        $got1 = true;
                    }
                } catch (Exception $e) {
                }
            }
            if (!$got2) {
                try {
                    $result2 = $this->connection->put('/_api/job/' . $id2, '');
                    if ($result2->getHeader('x-arango-async-id') !== null) {
                        $got2 = true;
                    }
                } catch (Exception $e) {
                }
            }

            if ($got1 && $got2) {
                break;
            }

            sleep(1);
        }


        static::assertTrue($got1);
        static::assertTrue($got2);

        $r1 = json_decode($result1->getBody());
        $r2 = json_decode($result2->getBody());
        static::assertTrue($r1->message === 29 || $r2->message === 29);
    }

    /**
     * Test if we can create and execute a transaction by using array initialization at construction time
     */
    public function testCreateAndExecuteTransactionWithArrayInitialization()
    {
        $writeCollections = [$this->collection1->getName()];
        $readCollections  = [$this->collection2->getName()];
        $action           = '
  function () {
    var db = require("internal").db;
    db.' . $this->collection1->getName() . '.save({ test : "hello" });
  }';
        $waitForSync      = true;
        $lockTimeout      = 10;

        $array       = [
            'collections' => ['read' => $readCollections, 'write' => $writeCollections],
            'action'      => $action,
            'waitForSync' => $waitForSync,
            'lockTimeout' => $lockTimeout
        ];
        $transaction = new Transaction($this->connection, $array);

        static::assertTrue(isset($transaction->action), 'Should return true, as the attribute was set, before.');

        // check if object was initialized correctly with the array
        static::assertEquals(
            $writeCollections, $transaction->getWriteCollections(), 'Did not return writeCollections, instead returned: ' . print_r($transaction->getWriteCollections(), 1)
        );
        static::assertEquals(
            $readCollections, $transaction->getReadCollections(), 'Did not return readCollections, instead returned: ' . print_r($transaction->getReadCollections(), 1)
        );
        static::assertEquals(
            $action, $transaction->getAction(), 'Did not return action, instead returned: ' . $transaction->getAction()
        );
        static::assertEquals(
            $waitForSync, $transaction->getWaitForSync(), 'Did not return waitForSync, instead returned: ' . $transaction->getWaitForSync()
        );
        static::assertEquals(
            $lockTimeout, $transaction->getLockTimeout(), 'Did not return lockTimeout, instead returned: ' . $transaction->getLockTimeout()
        );


        $result = $transaction->execute();
        static::assertTrue($result, 'Did not return true, instead returned: ' . $result);
    }


    /**
     * Test if we can create and execute a transaction by using magic getters/setters
     */
    public function testCreateAndExecuteTransactionWithMagicGettersSetters()
    {
        $writeCollections = [$this->collection1->getName()];
        $readCollections  = [$this->collection2->getName()];
        $action           = '
  function () {
    var db = require("internal").db;
    db.' . $this->collection1->getName() . '.save({ test : "hello" });
  }';
        $waitForSync      = true;
        $lockTimeout      = 10;

        // check if setters work fine
        $transaction                   = new Transaction($this->connection);
        $transaction->writeCollections = $writeCollections;
        $transaction->readCollections  = $readCollections;
        $transaction->action           = $action;
        $transaction->waitForSync      = true;
        $transaction->lockTimeout      = 10;

        // check if getters work fine

        static::assertEquals(
            $writeCollections, $transaction->writeCollections, 'Did not return writeCollections, instead returned: ' . print_r($transaction->writeCollections, 1)
        );
        static::assertEquals(
            $readCollections, $transaction->readCollections, 'Did not return readCollections, instead returned: ' . print_r($transaction->readCollections, 1)
        );
        static::assertEquals(
            $action, $transaction->action, 'Did not return action, instead returned: ' . $transaction->action
        );
        static::assertEquals(
            $waitForSync, $transaction->waitForSync, 'Did not return waitForSync, instead returned: ' . $transaction->waitForSync
        );
        static::assertEquals(
            $lockTimeout, $transaction->lockTimeout, 'Did not return lockTimeout, instead returned: ' . $transaction->lockTimeout
        );

        $result = $transaction->execute();
        static::assertTrue($result, 'Did not return true, instead returned: ' . $result);
    }


    /**
     * Test if we can create and execute a transaction by using magic getters/setters and single collection-definitions as strings
     */
    public function testCreateAndExecuteTransactionWithMagicGettersSettersAndSingleCollectionDefinitionsAsStrings()
    {
        $writeCollections = $this->collection1->getName();
        $readCollections  = $this->collection2->getName();
        $action           = '
  function () {
    var db = require("internal").db;
    db.' . $this->collection1->getName() . '.save({ test : "hello" });
  }';
        $waitForSync      = true;
        $lockTimeout      = 10;

        // check if setters work fine
        $transaction                   = new Transaction($this->connection);
        $transaction->writeCollections = $writeCollections;
        $transaction->readCollections  = $readCollections;
        $transaction->action           = $action;
        $transaction->waitForSync      = true;
        $transaction->lockTimeout      = 10;

        // check if getters work fine

        static::assertEquals(
            $writeCollections, $transaction->writeCollections, 'Did not return writeCollections, instead returned: ' . print_r($transaction->writeCollections, 1)
        );
        static::assertEquals(
            $readCollections, $transaction->readCollections, 'Did not return readCollections, instead returned: ' . print_r($transaction->readCollections, 1)
        );
        static::assertEquals(
            $action, $transaction->action, 'Did not return action, instead returned: ' . $transaction->action
        );
        static::assertEquals(
            $waitForSync, $transaction->waitForSync, 'Did not return waitForSync, instead returned: ' . $transaction->waitForSync
        );
        static::assertEquals(
            $lockTimeout, $transaction->lockTimeout, 'Did not return lockTimeout, instead returned: ' . $transaction->lockTimeout
        );

        $result = $transaction->execute();
        static::assertTrue($result, 'Did not return true, instead returned: ' . $result);
    }


    /**
     * Test if we can create and execute a transaction by using getters/setters
     */
    public function testCreateAndExecuteTransactionWithGettersSetters()
    {
        $writeCollections = [$this->collection1->getName()];
        $readCollections  = [$this->collection2->getName()];
        $action           = '
  function () {
    var db = require("internal").db;
    db.' . $this->collection1->getName() . '.save({ test : "hello" });
  }';
        $waitForSync      = true;
        $lockTimeout      = 10;


        $transaction = new Transaction($this->connection);

        // check if setters work fine
        $transaction->setWriteCollections($writeCollections);
        $transaction->setReadCollections($readCollections);
        $transaction->setAction($action);
        $transaction->setWaitForSync($waitForSync);
        $transaction->setLockTimeout($lockTimeout);

        // check if getters work fine

        static::assertEquals(
            $writeCollections, $transaction->getWriteCollections(), 'Did not return writeCollections, instead returned: ' . print_r($transaction->getWriteCollections(), 1)
        );
        static::assertEquals(
            $readCollections, $transaction->getReadCollections(), 'Did not return readCollections, instead returned: ' . print_r($transaction->getReadCollections(), 1)
        );
        static::assertEquals(
            $action, $transaction->getAction(), 'Did not return action, instead returned: ' . $transaction->getAction()
        );
        static::assertEquals(
            $waitForSync, $transaction->getWaitForSync(), 'Did not return waitForSync, instead returned: ' . $transaction->getWaitForSync()
        );
        static::assertEquals(
            $lockTimeout, $transaction->getLockTimeout(), 'Did not return lockTimeout, instead returned: ' . $transaction->getLockTimeout()
        );


        $result = $transaction->execute();
        static::assertTrue($result, 'Did not return true, instead returned: ' . $result);
    }


    /**
     * Test if we get the return-value from the code back.
     */
    public function testCreateAndExecuteTransactionWithReturnValue()
    {
        $writeCollections = [$this->collection1->getName()];
        $readCollections  = [$this->collection2->getName()];
        $action           = '
  function () {
    var db = require("internal").db;
    db.' . $this->collection1->getName() . '.save({ _key : "hello" });

    return "hello!!!";
  }';

        $transaction = new Transaction($this->connection);
        $transaction->setWriteCollections($writeCollections);
        $transaction->setReadCollections($readCollections);
        $transaction->setAction($action);

        $result = $transaction->execute();
        static::assertEquals('hello!!!', $result, 'Did not return hello!!!, instead returned: ' . $result);
    }


    /**
     * Test if we get an error back, if we throw an exception inside the transaction code
     */
    public function testCreateAndExecuteTransactionWithTransactionException()
    {
        $writeCollections = [$this->collection1->getName()];
        $readCollections  = [$this->collection2->getName()];
        $action           = '
  function () {
    var db = require("internal").db;
    db.' . $this->collection1->getName() . '.save({ test : "hello" });

    /* will abort and roll back the transaction */
    throw "doh!";
  }';

        $transaction = new Transaction($this->connection);
        $transaction->setWriteCollections($writeCollections);
        $transaction->setReadCollections($readCollections);
        $transaction->setAction($action);

        $e = null;
        try {
            $transaction->execute();
        } catch (ServerException $e) {
        }
        $details = $e->getDetails();

        static::assertSame(
            500, $e->getCode(), 'Did not return code 500, instead returned: ' . $e->getCode() . ' and ' . $details['errorMessage']
        );
    }


    /**
     * Test if we get an error back, if we violate a unique constraint
     */
    public function testCreateAndExecuteTransactionWithTransactionErrorUniqueConstraintOnSave()
    {
        if (isCluster($this->connection)) {
            // don't execute this test in a cluster
            $this->markTestSkipped("test is only meaningful in a single server");
            return;
        }

        $writeCollections = [$this->collection1->getName()];
        $readCollections  = [$this->collection2->getName()];
        $action           = '
  function () {
    var db = require("internal").db;
    db.' . $this->collection1->getName() . '.save({ _key : "hello" });
    db.' . $this->collection1->getName() . '.save({ _key : "hello" });
  }';

        $transaction = new Transaction($this->connection);
        $transaction->setWriteCollections($writeCollections);
        $transaction->setReadCollections($readCollections);
        $transaction->setAction($action);

        $e = null;
        try {
            $transaction->execute();
        } catch (ServerException $e) {
        }
        $details                = $e->getDetails();
        $expectedCutDownMessage = 'unique constraint violated';
        static::assertTrue(
            $e->getCode() === 409 && strpos(
                $details['errorMessage'],
                $expectedCutDownMessage
            ) !== false,
            'Did not return code 400 with first part of the message: "' . $expectedCutDownMessage . '", instead returned: ' . $e->getCode() . ' and "' . $details['errorMessage'] . '"'
        );
    }


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

        unset($this->collectionHandler, $this->collection1, $this->collection2, $this->connection);
    }
}
