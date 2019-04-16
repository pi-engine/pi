<?php
/**
 * ArangoDB PHP client testsuite
 * File: GraphExtendedTest.php
 *
 * @package ArangoDBClient
 * @author  Frank Mayer
 */

namespace ArangoDBClient;

/**
 * Class GraphExtendedTest
 * Extended Tests for the Graph API implementation
 *
 * @property Connection        $connection
 * @property Graph             $graph
 * @property Collection        $edgeCollection
 * @property CollectionHandler $collectionHandler
 * @property GraphHandler      $graphHandler
 * @property DocumentHandler   $documentHandler
 * @property EdgeHandler       $edgeHandler
 * @property string            vertex1Name
 * @property string            vertex2Name
 * @property string            vertex3Name
 * @property string            vertex4Name
 * @property string            vertex1aName
 * @property string            edge1Name
 * @property string            edge2Name
 * @property string            edge3Name
 * @property string            edge1aName
 * @property string            edgeLabel1
 * @property string            edgeLabel2
 * @property string            edgeLabel3
 * @property mixed             vertex1Array
 * @property mixed             vertex2Array
 * @property mixed             vertex3Array
 * @property mixed             vertex4Array
 * @property mixed             vertex1aArray
 * @property mixed             edge1Array
 * @property mixed             edge2Array
 * @property mixed             edge3Array
 * @property mixed             edge1aArray
 * @property string            graphName
 * @property string            vertexCollectionName
 * @property string            edgeCollectionName
 *
 * @package ArangoDBClient
 */
class GraphExtendedTest extends
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
        $this->vertex1Name  = 'vertex1';
        $this->vertex2Name  = 'vertex2';
        $this->vertex3Name  = 'vertex3';
        $this->vertex4Name  = 'vertex4';
        $this->vertex1aName = 'vertex1';
        $this->edge1Name    = 'edge1';
        $this->edge2Name    = 'edge2';
        $this->edge3Name    = 'edge3';
        $this->edge1aName   = 'edge1';
        $this->edgeLabel1   = 'edgeLabel1';
        $this->edgeLabel2   = 'edgeLabel2';
        $this->edgeLabel3   = 'edgeLabel3';


        $this->vertex1Array         = [
            '_key'     => $this->vertex1Name,
            'someKey1' => 'someValue1'
        ];
        $this->vertex2Array         = [
            '_key'     => $this->vertex2Name,
            'someKey2' => 'someValue2'
        ];
        $this->vertex3Array         = [
            '_key'     => $this->vertex3Name,
            'someKey3' => 'someValue3'
        ];
        $this->vertex4Array         = [
            '_key'     => $this->vertex4Name,
            'someKey4' => 'someValue4'
        ];
        $this->vertex1aArray        = [
            'someKey1' => 'someValue1a'
        ];
        $this->edge1Array           = [
            '_key'         => $this->edge1Name,
            'someEdgeKey1' => 'someEdgeValue1'
        ];
        $this->edge2Array           = [
            '_key'            => $this->edge2Name,
            'someEdgeKey2'    => 'someEdgeValue2',
            'anotherEdgeKey2' => 'anotherEdgeValue2'
        ];
        $this->edge3Array           = [
            '_key'         => $this->edge3Name,
            'someEdgeKey3' => 'someEdgeValue3'
        ];
        $this->edge1aArray          = [
            '_key'         => $this->edge1Name,
            'someEdgeKey1' => 'someEdgeValue1a'
        ];
        $this->vertexCollectionName = 'ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp;
        $this->edgeCollectionName   = 'ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp;

        $ed1 = EdgeDefinition::createUndirectedRelation($this->edgeCollectionName, [$this->vertexCollectionName]);

        $this->graphName  = 'Graph1' . '_' . static::$testsTimestamp;
        $this->connection = getConnection();
        $this->graph      = new Graph();
        $this->graph->set('_key', $this->graphName);
        $this->graph->addEdgeDefinition($ed1);
        $this->graphHandler = new GraphHandler($this->connection);
        $this->graphHandler->createGraph($this->graph);
    }


    // Helper method to setup a graph
    public function createGraph()
    {
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $vertex2 = Vertex::createFromArray($this->vertex2Array);
        $vertex3 = Vertex::createFromArray($this->vertex3Array);
        // This one is just an array to test if a vertex can be created with an array instead of a vertex object
        $vertex4 = $this->vertex4Array;
        $edge1   = Edge::createFromArray($this->edge1Array);
        $edge2   = Edge::createFromArray($this->edge2Array);
        // This one is just an array to test if an edge can be created with an array instead of an edge object
        $edge3 = $this->edge3Array;


        $this->graphHandler->saveVertex($this->graphName, $vertex1);
        $this->graphHandler->saveVertex($this->graphName, $vertex2);
        $this->graphHandler->saveVertex($this->graphName, $vertex3);
        $this->graphHandler->saveVertex($this->graphName, $vertex4);
        $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        $this->graphHandler->getVertex($this->graphName, $this->vertex3Name);
        $this->graphHandler->getVertex($this->graphName, $this->vertex4Name);
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->vertexCollectionName . '/' . $this->vertex1Name,
            $this->vertexCollectionName . '/' . $this->vertex2Name,
            $this->edgeLabel1,
            $edge1
        );
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->vertexCollectionName . '/' . $this->vertex2Name,
            $this->vertexCollectionName . '/' . $this->vertex3Name,
            $this->edgeLabel2,
            $edge2
        );
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->vertexCollectionName . '/' . $this->vertex3Name,
            $this->vertexCollectionName . '/' . $this->vertex4Name,
            $this->edgeLabel3,
            $edge3
        );
        $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        $this->graphHandler->getEdge($this->graphName, $this->edge2Name);
        $this->graphHandler->getEdge($this->graphName, $this->edge3Name);
    }


    /**
     * Test if 2 Vertices can be saved and an edge can be saved connecting them
     * Then remove in this order Edge, Vertex1, Vertex2
     */
    public function testSaveVerticesAndEdgeBetweenThemAndRemoveOneByOne()
    {
        // Setup Objects
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $vertex2 = Vertex::createFromArray($this->vertex2Array);
        $edge1   = Edge::createFromArray($this->edge1Array);


        // Save vertices
        $result1 = $this->graphHandler->saveVertex($this->graphName, $vertex1);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex1', $result1, 'Did not return vertex1!');

        $result2 = $this->graphHandler->saveVertex($this->graphName, $vertex2);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex2', $result2, 'Did not return vertex2!');


        // Get vertices
        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('vertex1', $result1->getKey(), 'Did not return vertex1!');

        $result2 = $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        static::assertEquals('vertex2', $result2->getKey(), 'Did not return vertex2!');


        // Save edge
        $resultE = $this->graphHandler->saveEdge(
            $this->graphName,
            $result1->getInternalId(),
            $result2->getInternalId(),
            $this->edgeLabel1,
            $edge1
        );
        static::assertEquals('ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp . '/edge1', $resultE, 'Did not return edge1!');


        // Get edge
        $resultE = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertEquals('edge1', $resultE->getKey(), 'Did not return edge1!');


        // Try to get the edge using GraphHandler
        $resultE = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertInstanceOf(Edge::class, $resultE);


        // Remove the edge
        $resultE = $this->graphHandler->removeEdge($this->graphName, $this->edge1Name);
        static::assertTrue($resultE, 'Did not return true!');


        // Remove one vertex using GraphHandler
        $result1 = $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        static::assertTrue($result1, 'Did not return true!');


        // Remove one vertex using GraphHandler | Testing
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to get vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Remove the other vertex using GraphHandler
        $result2 = $this->graphHandler->removeVertex($this->graphName, $this->vertex2Name);
        static::assertTrue($result2, 'Did not return true!');


        // Try to get vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());
    }


    /**
     * Test if 2 Vertices can be saved and an edge can be saved connecting them
     * Then remove in this order Edge, Vertex1, Vertex2
     */
    public function testSaveVerticesAndEdgeBetweenThemAndRemoveOneByOneWithCache()
    {
        // Setup Objects
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $vertex2 = Vertex::createFromArray($this->vertex2Array);
        $edge1   = Edge::createFromArray($this->edge1Array);

        $this->graphHandler->setCacheEnabled(true);
        // Save vertices
        $result1 = $this->graphHandler->saveVertex($this->graphName, $vertex1);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex1', $result1, 'Did not return vertex1!');

        $result2 = $this->graphHandler->saveVertex($this->graphName, $vertex2);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex2', $result2, 'Did not return vertex2!');


        // Get vertices
        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('vertex1', $result1->getKey(), 'Did not return vertex1!');

        $result2 = $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        static::assertEquals('vertex2', $result2->getKey(), 'Did not return vertex2!');


        // Save edge
        $resultE = $this->graphHandler->saveEdge(
            $this->graphName,
            $result1->getInternalId(),
            $result2->getInternalId(),
            $this->edgeLabel1,
            $edge1
        );
        static::assertEquals('ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp . '/edge1', $resultE, 'Did not return edge1!');


        // Get edge
        $resultE = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertEquals('edge1', $resultE->getKey(), 'Did not return edge1!');


        // Try to get the edge using GraphHandler
        $resultE = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertInstanceOf(Edge::class, $resultE);


        // Remove the edge
        $resultE = $this->graphHandler->removeEdge($this->graphName, $this->edge1Name);
        static::assertTrue($resultE, 'Did not return true!');


        // Remove one vertex using GraphHandler
        $result1 = $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        static::assertTrue($result1, 'Did not return true!');


        // Remove one vertex using GraphHandler | Testing
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to get vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Remove the other vertex using GraphHandler
        $result2 = $this->graphHandler->removeVertex($this->graphName, $this->vertex2Name);
        static::assertTrue($result2, 'Did not return true!');


        // Try to get vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());
    }


    /**
     * Test if 2 Vertices can be saved and an edge can be saved connecting them, but remove the first vertex first
     * This should throw an exception on removing the edge, because it will be removed with
     */
    public function testSaveVerticesAndEdgeBetweenThemAndRemoveFirstVertexFirst()
    {
        // Setup Objects
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $vertex2 = Vertex::createFromArray($this->vertex2Array);
        $edge1   = Edge::createFromArray($this->edge1Array);


        // Save vertices
        $result1 = $this->graphHandler->saveVertex($this->graphName, $vertex1);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex1', $result1, 'Did not return vertex1!');

        $result2 = $this->graphHandler->saveVertex($this->graphName, $vertex2);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex2', $result2, 'Did not return vertex2!');


        // Get vertices
        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('vertex1', $result1->getKey(), 'Did not return vertex1!');

        $result2 = $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        static::assertEquals('vertex2', $result2->getKey(), 'Did not return vertex2!');


        // Save edge
        $result1 = $this->graphHandler->saveEdge(
            $this->graphName,
            $result1->getInternalId(),
            $result2->getInternalId(),
            $this->edgeLabel1,
            $edge1
        );
        static::assertEquals($result1, 'ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp . '/edge1', 'Did not return edge1!');


        // Get edge
        $result1 = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertEquals('edge1', $result1->getKey(), 'Did not return edge1!');


        // Remove one vertex using GraphHandler
        $result1a = $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        static::assertTrue($result1a, 'Did not return true!');


        // Remove the same vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to get vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to get the edge using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to remove the edge using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->removeEdge($this->graphName, $this->edge1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Remove the other vertex using GraphHandler
        $result2 = $this->graphHandler->removeVertex($this->graphName, $this->vertex2Name);
        static::assertTrue($result2, 'Did not return true!');


        // Try to get vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());
    }


    /**
     * Test for correct exception codes if nonexistent objects are tried to be gotten, replaced, updated or removed
     */
    public function testGetReplaceUpdateAndRemoveOnNonExistentObjects()
    {
        // Setup objects
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $edge1   = Edge::createFromArray($this->edge1Array);


        // Try to get vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to update vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->updateVertex($this->graphName, $this->vertex1Name, $vertex1);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to replace vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->replaceVertex($this->graphName, $this->vertex1Name, $vertex1);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Remove a vertex using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to get the edge using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to update edge using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->updateEdge($this->graphName, $this->edge1Name, 'label', $edge1);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to replace edge using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->replaceEdge($this->graphName, $this->edge1Name, 'label', $edge1);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());


        // Try to remove the edge using GraphHandler
        // This should cause an exception with a code of 404
        try {
            $e = null;
            $this->graphHandler->removeEdge($this->graphName, $this->edge1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
        static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());
    }


    /**
     * Test if a Vertex can be saved, replaced, updated, and finally removed
     */
    public function testSaveVertexReplaceUpdateAndRemove()
    {
        // Setup Objects
        $vertex1  = Vertex::createFromArray($this->vertex1Array);
        $vertex2  = Vertex::createFromArray($this->vertex2Array);
        $vertex1a = Vertex::createFromArray($this->vertex1aArray);


        // Save vertices
        $result1 = $this->graphHandler->saveVertex($this->graphName, $vertex1);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex1', $result1, 'Did not return vertex1!');

        $result2 = $this->graphHandler->saveVertex($this->graphName, $vertex2);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex2', $result2, 'Did not return vertex2!');


        // Get vertices
        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('vertex1', $result1->getKey(), 'Did not return vertex1!');

        $result2 = $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        static::assertEquals('vertex2', $result2->getKey(), 'Did not return vertex2!');


        // Replace vertex
        $result1a = $this->graphHandler->replaceVertex($this->graphName, $this->vertex1Name, $vertex1a);
        static::assertTrue($result1a, 'Did not return true!');


        // Get vertex
        $result1a = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('someValue1a', $result1a->someKey1, 'Did not return someValue1a!');


        // Replace vertex
        $result1 = $this->graphHandler->replaceVertex($this->graphName, $this->vertex1Name, $vertex1);
        static::assertTrue($result1, 'Did not return true!');


        // Get vertex
        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('someValue1', $result1->someKey1, 'Did not return someValue1!');


        $result1a = $this->graphHandler->updateVertex($this->graphName, $this->vertex1Name, $vertex1a);
        static::assertTrue($result1a, 'Did not return true!');


        $result1a = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('someValue1a', $result1a->someKey1, 'Did not return someValue1a!');


        $result1 = $this->graphHandler->updateVertex($this->graphName, $this->vertex1Name, $vertex1);
        static::assertTrue($result1, 'Did not return true!');


        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('someValue1', $result1->someKey1, 'Did not return someValue1!');


        $result1a = $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        static::assertTrue($result1a, 'Did not return true!');


        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);


        $result2 = $this->graphHandler->removeVertex($this->graphName, $this->vertex2Name);
        static::assertTrue($result2, 'Did not return true!');


        try {
            $e = null;
            $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
    }

    /**
     * Test if a Vertex can be saved, replaced, updated, and finally removed with conditions
     */
    public function testSaveVertexConditionalReplaceUpdateAndRemove()
    {
        // Setup Objects
        $vertex1  = Vertex::createFromArray($this->vertex1Array);
        $vertex2  = Vertex::createFromArray($this->vertex2Array);
        $vertex1a = Vertex::createFromArray($this->vertex1aArray);


        // Save vertices
        $result1 = $this->graphHandler->saveVertex($this->graphName, $vertex1);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex1', $result1, 'Did not return vertex1!');

        $result2 = $this->graphHandler->saveVertex($this->graphName, $vertex2);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex2', $result2, 'Did not return vertex2!');


        // Get vertices
        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('vertex1', $result1->getKey(), 'Did not return vertex1!');

        $result2 = $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        static::assertEquals('vertex2', $result2->getKey(), 'Did not return vertex2!');


        // Replace vertex
        $result1a = $this->graphHandler->replaceVertex(
            $this->graphName,
            $this->vertex1Name,
            $vertex1a,
            ['revision' => $result1->getRevision()]
        );
        static::assertTrue($result1a, 'Did not return true!');


        // Get vertex
        $result1a = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('someValue1a', $result1a->someKey1, 'Did not return someValue1a!');


        // Replace vertex
        $result1 = $this->graphHandler->replaceVertex($this->graphName, $this->vertex1Name, $vertex1);
        static::assertTrue($result1, 'Did not return true!');


        // Get vertex
        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('someValue1', $result1->someKey1, 'Did not return someValue1!');


        $result1a = $this->graphHandler->updateVertex($this->graphName, $this->vertex1Name, $vertex1a);
        static::assertTrue($result1a, 'Did not return true!');


        $result1a = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('someValue1a', $result1a->someKey1, 'Did not return someValue1a!');

        $e = null;

        try {
            $result1 = $this->graphHandler->updateVertex(
                $this->graphName,
                $this->vertex1Name,
                $vertex1,
                ['revision' => true]
            );
            static::assertTrue($result1, 'Did not return true!');
        } catch (Exception $e) {
            //just give us the $e
        }
        static::assertInstanceOf(
            ServerException::class,
            $e,
            'An exception should be thrown by the mis-matching revision!'
        );

        $result1a = $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        static::assertTrue($result1a, 'Did not return true!');


        $result2 = $this->graphHandler->removeVertex($this->graphName, $this->vertex2Name);
        static::assertTrue($result2, 'Did not return true!');
    }

    /**
     * Test if 2 Vertices can be saved and an Edge between them can be saved, replaced, updated, and finally removed
     */
    public function testSaveVerticesAndSaveReplaceUpdateAndRemoveEdge()
    {
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $vertex2 = Vertex::createFromArray($this->vertex2Array);
        $edge1   = Edge::createFromArray($this->edge1Array);
        $edge1a  = Edge::createFromArray($this->edge1aArray);


        $result1 = $this->graphHandler->saveVertex($this->graphName, $vertex1);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex1', $result1, 'Did not return vertex1!');


        $result2 = $this->graphHandler->saveVertex($this->graphName, $vertex2);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex2', $result2, 'Did not return vertex2!');


        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('vertex1', $result1->getKey(), 'Did not return vertex1!');


        $result2 = $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        static::assertEquals('vertex2', $result2->getKey(), 'Did not return vertex2!');


        $result1 = $this->graphHandler->saveEdge(
            $this->graphName,
            $result1->getInternalId(),
            $result2->getInternalId(),
            $this->edgeLabel1,
            $edge1
        );
        static::assertEquals($this->edgeCollectionName . '/edge1', $result1, 'Did not return edge1!');


        $result1 = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertEquals('edge1', $result1->getKey(), 'Did not return edge1!');

        $edge1a->setFrom($result1->getInternalId());
        $edge1a->setTo($result2->getInternalId());

        $result1a = $this->graphHandler->replaceEdge($this->graphName, $this->edge1Name, $this->edgeLabel1, $edge1a);
        static::assertTrue($result1a, 'Did not return true!');


        $result1a = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertEquals('someEdgeValue1a', $result1a->someEdgeKey1, 'Did not return someEdgeValue1a!');


        $result1a = $this->graphHandler->updateEdge($this->graphName, $this->edge1Name, $this->edgeLabel1, $edge1);
        static::assertTrue($result1a, 'Did not return true!');


        $result1 = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertEquals('someEdgeValue1', $result1->someEdgeKey1, 'Did not return someEdgeValue1!');


        $result1a = $this->graphHandler->removeEdge($this->graphName, $this->edge1Name);
        static::assertTrue($result1a, 'Did not return true!');

        $e = null;
        try {
            $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);


        $result1a = $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        static::assertTrue($result1a, 'Did not return true!');


        $e = null;
        try {
            $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);


        $result2 = $this->graphHandler->removeVertex($this->graphName, $this->vertex2Name);
        static::assertTrue($result2, 'Did not return true!');


        $e = null;
        try {
            $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);
    }

    /**
     * Test if 2 Vertices can be saved and an Edge between them can be saved, replaced, updated, and finally removed conditionally
     */
    public function testSaveVerticesAndConditionalSaveReplaceUpdateAndRemoveEdge()
    {
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $vertex2 = Vertex::createFromArray($this->vertex2Array);
        $edge1   = Edge::createFromArray($this->edge1Array);
        $edge1a  = Edge::createFromArray($this->edge1aArray);


        $result1 = $this->graphHandler->saveVertex($this->graphName, $vertex1);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex1', $result1, 'Did not return vertex1!');


        $result2 = $this->graphHandler->saveVertex($this->graphName, $vertex2);
        static::assertEquals('ArangoDB_PHP_TestSuite_VertexTestCollection_01' . '_' . static::$testsTimestamp . '/vertex2', $result2, 'Did not return vertex2!');


        $result1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Name);
        static::assertEquals('vertex1', $result1->getKey(), 'Did not return vertex1!');


        $result2 = $this->graphHandler->getVertex($this->graphName, $this->vertex2Name);
        static::assertEquals('vertex2', $result2->getKey(), 'Did not return vertex2!');


        $result1 = $this->graphHandler->saveEdge(
            $this->graphName,
            $result1->getInternalId(),
            $result2->getInternalId(),
            $this->edgeLabel1,
            $edge1
        );
        static::assertEquals('ArangoDB_PHP_TestSuite_TestEdgeCollection_01' . '_' . static::$testsTimestamp . '/edge1', $result1, 'Did not return edge1!');


        $result1 = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertEquals('edge1', $result1->getKey(), 'Did not return edge1!');

        $edge1a->setFrom($result1->getInternalId());
        $edge1a->setTo($result2->getInternalId());


        $result1a = $this->graphHandler->replaceEdge(
            $this->graphName,
            $this->edge1Name,
            $this->edgeLabel1,
            $edge1a,
            ['revision' => $result1->getRevision()]
        );
        static::assertTrue($result1a, 'Did not return true!');


        $result1a = $this->graphHandler->getEdge($this->graphName, $this->edge1Name);
        static::assertEquals('someEdgeValue1a', $result1a->someEdgeKey1, 'Did not return someEdgeValue1a!');

        $e = null;
        try {
            $this->graphHandler->updateEdge(
                $this->graphName,
                $this->edge1Name,
                $this->edgeLabel1,
                $edge1,
                ['revision' => true]
            );
        } catch (Exception $e) {
            //Just give the $e
        }

        static::assertInstanceOf(
            ServerException::class,
            $e,
            'An exception should be thrown by the mis-matching revision!'
        );

        $result1a = $this->graphHandler->removeVertex($this->graphName, $this->vertex1Name);
        static::assertTrue($result1a, 'Did not return true!');

        $result2 = $this->graphHandler->removeVertex($this->graphName, $this->vertex2Name);
        static::assertTrue($result2, 'Did not return true!');
    }


    /**
     * Test if two Vertices can be saved and an edge can be saved connecting them but with the document & edge-handlers instead of the graphHandler
     * Then remove all starting with vertex1 first
     * There is no need for another test with handlers other than as the GraphHandler since there is no automatic edge-removal functionality when removing a vertex
     */
    public function testSaveVerticesFromVertexHandlerAndEdgeFromEdgeHandlerBetweenThemAndRemoveFirstVertexFirst()
    {
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $vertex2 = Vertex::createFromArray($this->vertex2Array);
        $edge1   = Edge::createFromArray($this->edge1Array);

        $vertexHandler = new VertexHandler($this->connection);

        // Save vertices using VertexHandler
        $result1 = $vertexHandler->save($this->vertexCollectionName, $vertex1);
        static::assertEquals($this->vertexCollectionName.'/vertex1', $result1, 'Did not return vertex1!');


        $result2 = $vertexHandler->save($this->vertexCollectionName, $vertex2);
        static::assertEquals($this->vertexCollectionName.'/vertex2', $result2, 'Did not return vertex2!');


        // Get vertices using VertexHandler
        $result1 = $vertexHandler->getById($this->vertexCollectionName, $this->vertex1Name);
        static::assertEquals('vertex1', $result1->getKey(), 'Did not return vertex1!');


        $result2 = $vertexHandler->getById($this->vertexCollectionName, $this->vertex2Name);
        static::assertEquals('vertex2', $result2->getKey(), 'Did not return vertex2!');


        // Save edge using EdgeHandler
        $edgeHandler = new EdgeHandler($this->connection);
        $result1     = $edgeHandler->saveEdge(
            $this->edgeCollectionName,
            $this->vertexCollectionName . '/' . $this->vertex1Name,
            $this->vertexCollectionName . '/' . $this->vertex2Name,
            $edge1
        );
        static::assertEquals($this->edgeCollectionName.'/edge1', $result1, 'Did not return edge1!');


        // Get edge using EdgeHandler
        $result1 = $edgeHandler->getById($this->edgeCollectionName, $this->edge1Name);
        static::assertEquals('edge1', $result1->getKey(), 'Did not return edge1!');


        // Remove one vertex using VertexHandler
        $result1a = $vertexHandler->removeById($this->vertexCollectionName, $this->vertex1Name);
        static::assertTrue($result1a, 'Did not return true!');


        // Try to get vertex using VertexHandler
        // This should cause an exception with a code of 404
        $e = null;
        try {
            $vertexHandler->getById($this->vertexCollectionName, $this->vertex1Name);
        } catch (\Exception $e) {
            // don't bother us... just give us the $e
        }
        static::assertInstanceOf(ServerException::class, $e);


        // Try to get the edge using EdgeHandler
        // This should cause an exception with a code of 404, because connecting edges should be removed when a vertex is removed
        $e = null;
        try {
            $edgeHandler->getById($this->edgeCollectionName, $this->edge1Name);
        } catch (\Exception $e) {
            static::assertInstanceOf(ServerException::class, $e);
            static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());
        }


        // Try to remove the edge using EdgeHandler
        // This should not cause an exception with a code of 404, because the we removed the vertex through the VertexHandler, not the GraphHandler
        try {
            $result = $edgeHandler->removeById($this->edgeCollectionName, $this->edge1Name);
        } catch (\Exception $e) {
            $result = $e;
        }
        static::assertTrue($result, 'Should be true, instead got: ' . $result);


        // Try to remove the edge using VertexHandler again
        // This should not cause an exception with code 404 because we just had removed this edge
        $e = null;
        try {
            $edgeHandler->removeById($this->edgeCollectionName, $this->edge1Name);
        } catch (\Exception $e) {
            static::assertInstanceOf(ServerException::class, $e);
            static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());
        }


        // Remove the other vertex using VertexHandler
        $result2 = $vertexHandler->removeById($this->vertexCollectionName, $this->vertex2Name);
        static::assertTrue($result2, 'Did not return true!');


        // Try to get vertex using VertexHandler
        // This should cause an exception with a code of 404
        $e = null;
        try {
            $vertexHandler->getById($this->vertexCollectionName, $this->vertex2Name);
        } catch (\Exception $e) {
            static::assertInstanceOf(ServerException::class, $e);
            static::assertEquals(404, $e->getCode(), 'Should be 404, instead got: ' . $e->getCode());
        }
    }


    /**
     * Tests if the saveVertex method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testSaveVertexWithGraphInstance()
    {
        $id = $this->graphHandler->saveVertex($this->graph, $this->vertex1Array);
        static::assertEquals($this->vertexCollectionName . '/vertex1', $id);
    }

    /**
     * Tests if the getVertex method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testGetVertexWithGraphInstance()
    {
        $this->createGraph();
        static::assertEquals('vertex1', $this->graphHandler->getVertex($this->graph, 'vertex1')->getKey());
    }

    /**
     * Tests if the replaceVertex method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testReplaceVertexWithGraphInstance()
    {
        $this->createGraph();
        $new = Vertex::createFromArray(
            [
                '_key'    => 'testreplacewithgraphinstancekey',
                'someKey' => 'someValue'
            ]
        );
        static::assertTrue($this->graphHandler->replaceVertex($this->graph, 'vertex1', $new));
    }

    /**
     * Tests if the updateVertex method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testUpdateVertexWithGraphInstance()
    {
        $this->createGraph();
        $new = Vertex::createFromArray(
            [
                '_key'    => 'vertex1',
                'someKey' => 'foobar'
            ]
        );
        static::assertTrue($this->graphHandler->updateVertex($this->graph, 'vertex1', $new));
    }

    /**
     * Tests if the removeVertex method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testRemoveVertexWithGraphInstance()
    {
        $this->createGraph();
        static::assertTrue($this->graphHandler->removeVertex($this->graph, 'vertex1'));
    }

    /**
     * Tests if the saveEde method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testSaveEdgeWithGraphInstance()
    {
        $this->createGraph();
        $id = $this->graphHandler->saveEdge(
            $this->graph, $this->vertexCollectionName . '/' . $this->vertex1Name,
            $this->vertexCollectionName . '/' . $this->vertex2Name, 'foobaredge', ['_key' => 'foobaredgekey']
        );
        static::assertEquals($this->edgeCollectionName . '/' . 'foobaredgekey', $id);
    }

    /**
     * Tests if the getEdge method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testGetEdgeWithGraphInstance()
    {
        $this->createGraph();
        $edge = $this->graphHandler->getEdge($this->graph, $this->edge1Name);
        static::assertEquals($this->edge1Name, $edge->getKey());
    }

    /**
     * Tests if the replaceEdge method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testReplaceEdgeWithGraphInstance()
    {
        $this->createGraph();
        $edge    = $this->graphHandler->getEdge($this->graph, $this->edge1Name);
        $newEdge = Edge::createFromArray(['_key' => 'foobar']);
        $newEdge->setFrom($edge->getFrom());
        $newEdge->setTo($edge->getTo());
        $result = $this->graphHandler->replaceEdge($this->graph, $this->edge1Name, '', $newEdge);
        static::assertTrue($result);
    }

    /**
     * Tests if the updateEdge method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testUpdateEdgeWithGraphInstance()
    {
        $this->createGraph();
        $result = $this->graphHandler->updateEdge($this->graph, $this->edge1Name, '', Edge::createFromArray(['_key' => 'foobar']));
        static::assertTrue($result);
    }

    /**
     * Tests if the removeEdge method accepts an instance of Graph as first argument and extracts the graph name out of it.
     */
    public function testRemoveEdgeWithGraphInstance()
    {
        $this->createGraph();
        $result = $this->graphHandler->removeEdge($this->graph, $this->edge1Name);
        static::assertTrue($result);
    }

    public function testHasVertexReturnsFalseIfNotExists()
    {
        $result = $this->graphHandler->hasVertex($this->graphName, 'just_a_stupid_vertex_id_which_does_not_exist');
        static::assertFalse($result);
    }

    public function testHasVertexReturnsTrueIfExists()
    {
        $this->createGraph();
        $result = $this->graphHandler->hasVertex($this->graphName, $this->vertex1Name);
        static::assertTrue($result);
    }

    public function testHasEdgeReturnsFalseIfNotExists()
    {
        $result = $this->graphHandler->hasEdge($this->graphName, 'just_a_stupid_edge_id_which_does_not_exist');
        static::assertFalse($result);
    }

    public function testHasEdgeReturnsTrueIfExists()
    {
        $this->createGraph();
        $result = $this->graphHandler->hasEdge($this->graphName, $this->edge1Name);
        static::assertTrue($result);
    }

    public function tearDown()
    {
        try {
            $result = $this->graphHandler->dropGraph($this->graphName);
            static::assertTrue($result, 'Did not return true!');
        } catch (\Exception $e) {
            // don't bother us, if it's already deleted.
        }

        unset($this->graph, $this->graphHandler, $this->connection);
    }
}
