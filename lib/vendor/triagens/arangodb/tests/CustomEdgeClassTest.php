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
class CustomEdgeClassTest extends
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
        $this->collection->setType('edge');
        $this->collectionHandler->create($this->collection);
    }


    /**
     * Try to retrieve an edge with custom document class
     */
    public function testGetCustomEdgeWithHandler()
    {
        $connection      = $this->connection;
        $collection      = $this->collection;
        $edge            = new Edge();
        $edgeHandler     = new EdgeHandler($connection);

        $edge->someAttribute = 'someValue';
        $edge->setFrom('test/v1');
        $edge->setTo('test/v2');
        $documentId = $edgeHandler->saveEdge($collection->getName(), 'test/v1', 'test/v2', $edge);

        $edgeHandler->setEdgeClass(CustomEdgeClass1::class);
        $resultingEdge1 = $edgeHandler->get($collection->getName(), $documentId);
        static::assertInstanceOf(CustomEdgeClass1::class, $resultingEdge1, 'Retrieved edge isn\'t made with provided CustomEdgeClass1!');

        $edgeHandler->setEdgeClass(CustomEdgeClass2::class);
        $resultingEdge2 = $edgeHandler->get($collection->getName(), $documentId);
        static::assertInstanceOf(CustomEdgeClass2::class, $resultingEdge2, 'Retrieved edge isn\'t made with provided CustomEdgeClass2!');

        $edgeHandler->setEdgeClass(Edge::class);
        $resultingEdge = $edgeHandler->get($collection->getName(), $documentId);
        static::assertInstanceOf(Edge::class, $resultingEdge, 'Retrieved edge isn\'t made with provided Edge!');
        static::assertNotInstanceOf(CustomEdgeClass1::class, $resultingEdge, 'Retrieved edge is made with CustomEdgeClass1!');
        static::assertNotInstanceOf(CustomEdgeClass2::class, $resultingEdge, 'Retrieved edge is made with CustomEdgeClass2!');

        $resultingAttribute = $resultingEdge->someAttribute;
        static::assertSame('someValue', $resultingAttribute);
        static::assertSame('test/v1', $resultingEdge->getFrom());
        static::assertSame('test/v2', $resultingEdge->getTo());

        $resultingAttribute1 = $resultingEdge1->someAttribute;
        static::assertSame('someValue', $resultingAttribute1);
        static::assertSame('test/v1', $resultingEdge1->getFrom());
        static::assertSame('test/v2', $resultingEdge1->getTo());

        $resultingAttribute2 = $resultingEdge2->someAttribute;
        static::assertSame('someValue', $resultingAttribute2);
        static::assertSame('test/v1', $resultingEdge2->getFrom());
        static::assertSame('test/v2', $resultingEdge2->getTo());

        $edgeHandler->remove($edge);
    }

    public function tearDown()
    {
        try {
            $this->collectionHandler->drop('ArangoDB_PHP_TestSuite_TestCollection_01' . '_' . static::$testsTimestamp);
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }
    }

}

class CustomEdgeClass1 extends Edge
{
}

class CustomEdgeClass2 extends Edge
{
}
