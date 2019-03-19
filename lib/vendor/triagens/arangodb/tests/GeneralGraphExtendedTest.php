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
 * @property GraphHandler graphHandler
 * @property Connection   connection
 * @property string       graphName
 * @package ArangoDBClient
 */
class GeneralGraphExtendedTest extends
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
        $v                = time();
        $this->graphName  = 'graph' . $v . '_' . static::$testsTimestamp;
        $this->v1         = 'v1' . $v;
        $this->v2         = 'v2' . $v;
        $this->v3         = 'v3' . $v;
        $this->v4         = 'v4' . $v;
        $this->v5         = 'v5' . $v;
        $this->e1         = 'e1' . $v;
        $this->e2         = 'e2' . $v;
        $param1           = [];
        $param1[]         = $this->v1;
        $param1[]         = $this->v2;
        $param2           = [];
        $param2[]         = $this->v3;
        $param2[]         = $this->v4;
        $ed1              = EdgeDefinition::createDirectedRelation(
            $this->e1,
            $param1,
            $param2
        );
        $ed2              = EdgeDefinition::createUndirectedRelation(
            $this->e2, $this->v5
        );
        $this->graph      = new Graph($this->graphName);
        $this->graph->addEdgeDefinition($ed1);
        $this->graph->addEdgeDefinition($ed2);
        $this->graph->addOrphanCollection('orphan' . '_' . static::$testsTimestamp);
        $this->graphHandler = new GraphHandler($this->connection);
        $this->graphHandler->createGraph($this->graph);
        $this->graph        = $this->graphHandler->getGraph($this->graphName);
        $this->vertex1Array = [
            '_key'       => 'vertex1',
            'someKey1'   => 'someValue1',
            'sharedKey1' => 1
        ];
        $this->vertex2Array = [
            '_key'       => 'vertex2',
            'someKey2'   => 'someValue2',
            'sharedKey1' => 2
        ];
        $this->vertex3Array = [
            '_key'       => 'vertex3',
            'someKey3'   => 'someValue3',
            'sharedKey1' => 1
        ];
        $this->vertex4Array = [
            '_key'       => 'vertex4',
            'someKey4'   => 'someValue4',
            'sharedKey1' => 2
        ];
        $this->vertex5Array = [
            '_key'       => 'vertex5',
            'someKey5'   => 'someValue5',
            'a'          => 3,
            'sharedKey1' => 1
        ];
        $this->vertex6Array = [
            '_key'       => 'vertex6',
            'someKey6'   => 'someValue6',
            'sharedKey1' => 1
        ];
        $this->vertex7Array = [
            '_key'       => 'vertex7',
            'someKey7'   => 'someValue7',
            'a'          => 3,
            'sharedKey1' => 1
        ];
        $this->edge1Array   = [
            '_key'         => 'edge1',
            'someEdgeKey1' => 'someEdgeValue1',
            'sharedKey1'   => 1,
            'weight'       => 10
        ];
        $this->edge2Array   = [
            '_key'         => 'edge2',
            'someEdgeKey2' => 'someEdgeValue2',
            'sharedKey2'   => 2,
            'weight'       => 15
        ];
        $this->edge3Array   = [
            '_key'       => 'edge3',
            'sharedKey3' => 2,
            'weight'     => 12
        ];
        $this->edge4Array   = [
            '_key'       => 'edge4',
            'sharedKey4' => 1,
            'weight'     => 7
        ];
        $this->edge5Array   = [
            '_key'       => 'edge5',
            'sharedKey5' => 1,
            'weight'     => 5
        ];
        $this->edge6Array   = [
            '_key'       => 'edge6',
            'sharedKey6' => 1,
            'weight'     => 2
        ];
    }


    // Helper method to setup a graph
    public function createGraph()
    {
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $vertex2 = Vertex::createFromArray($this->vertex2Array);
        $vertex3 = Vertex::createFromArray($this->vertex3Array);
        $vertex4 = Vertex::createFromArray($this->vertex4Array);
        $vertex5 = Vertex::createFromArray($this->vertex5Array);
        $vertex6 = Vertex::createFromArray($this->vertex6Array);
        $vertex7 = Vertex::createFromArray($this->vertex7Array);
        $edge1   = Edge::createFromArray($this->edge1Array);
        $edge2   = Edge::createFromArray($this->edge2Array);
        $edge3   = Edge::createFromArray($this->edge3Array);
        $edge4   = Edge::createFromArray($this->edge4Array);
        $edge5   = Edge::createFromArray($this->edge5Array);
        $this->graphHandler->saveVertex($this->graphName, $vertex1, $this->v1);
        $this->graphHandler->saveVertex($this->graphName, $vertex2, $this->v2);
        $this->graphHandler->saveVertex($this->graphName, $vertex3, $this->v3);
        $this->graphHandler->saveVertex($this->graphName, $vertex4, $this->v4);
        $this->graphHandler->saveVertex($this->graphName, $vertex5, $this->v5);
        $this->graphHandler->saveVertex($this->graphName, $vertex6, $this->v5);
        $this->graphHandler->saveVertex($this->graphName, $vertex7, $this->v1);
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->v1 . '/' . $this->vertex1Array['_key'],
            $this->v3 . '/' . $this->vertex3Array['_key'],
            'edgeLabel1',
            $edge1,
            $this->e1
        );
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->v1 . '/' . $this->vertex7Array['_key'],
            $this->v3 . '/' . $this->vertex3Array['_key'],
            'edgeLabel2',
            $edge2,
            $this->e1
        );
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->v1 . '/' . $this->vertex7Array['_key'],
            $this->v4 . '/' . $this->vertex4Array['_key'],
            'edgeLabel3',
            $edge3,
            $this->e1
        );
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->v2 . '/' . $this->vertex2Array['_key'],
            $this->v4 . '/' . $this->vertex4Array['_key'],
            'edgeLabel4',
            $edge4,
            $this->e1
        );
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->v5 . '/' . $this->vertex5Array['_key'],
            $this->v5 . '/' . $this->vertex6Array['_key'],
            'edgeLabel5',
            $edge5,
            $this->e2
        );
    }


    /**
     */
    public function testsaveGetUpdateReplaceRemoveVertex()
    {
        $vertex1 = Vertex::createFromArray($this->vertex1Array);
        $ex      = null;
        try {
            $this->graphHandler->saveVertex($this->graphName, $vertex1);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);
        $this->createGraph();

        $ex = null;
        try {
            $this->graphHandler->getVertex($this->graphName, $this->vertex1Array['_key']);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);

        $v1 = $this->graphHandler->getVertex($this->graphName, $this->vertex1Array['_key'], [], $this->v1);
        $v2 = $this->graphHandler->getVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], []);

        static::assertEquals($v1->getInternalKey(), $v2->getInternalKey());

        $v1 = $this->graphHandler->getVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], []);
        $v  = Vertex::createFromArray($this->vertex7Array);
        $v->setRevision($v1->getRevision());
        $ex = null;
        try {
            $this->graphHandler->replaceVertex($this->graphName, $this->vertex1Array['_key'], $v, []);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);

        $v1 = $this->graphHandler->getVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], []);
        $v  = Vertex::createFromArray($this->vertex7Array);
        $v->setRevision($v1->getRevision());
        static::assertTrue(
            $this->graphHandler->replaceVertex($this->graphName, $this->vertex1Array['_key'], $v, ['revision' => $v1->getRevision()], $this->v1)
        );
        $v1 = $this->graphHandler->getVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], []);
        $v  = Vertex::createFromArray($this->vertex7Array);
        $v->setRevision($v1->getRevision());
        static::assertTrue(
            $this->graphHandler->replaceVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], $v, ['revision' => true])
        );
        $ex = null;
        try {
            $this->graphHandler->updateVertex($this->graphName, $this->vertex1Array['_key'], $v, []);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);
        $v1 = $this->graphHandler->getVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], []);
        $v  = Vertex::createFromArray($this->vertex7Array);
        $v->setRevision($v1->getRevision());
        static::assertTrue(
            $this->graphHandler->updateVertex($this->graphName, $this->vertex1Array['_key'], $v, ['revision' => true], $this->v1)
        );
        $v1 = $this->graphHandler->getVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], []);
        $v  = Vertex::createFromArray($this->vertex7Array);
        $v->setRevision($v1->getRevision());
        static::assertTrue(
            $this->graphHandler->updateVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], $v, ['revision' => $v1->getRevision()])
        );
        //removeVertex($graph, $vertexId, $revision = null, $options = [], $collection = null)
        $ex = null;
        try {
            $this->graphHandler->removeVertex($this->graphName, $this->vertex1Array['_key'], null, []);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);
        $v1 = $this->graphHandler->getVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], []);
        static::assertTrue($this->graphHandler->removeVertex($this->graphName, $this->v1 . '/' . $this->vertex1Array['_key'], $v1->getRevision(), []));


    }

    /**
     */
    public function testsaveGetUpdateReplaceRemoveEdge()
    {
        $edge1 = Edge::createFromArray($this->edge1Array);
        $ex    = null;
        try {
            $this->graphHandler->saveEdge(
                $this->graphName,
                $this->v1 . '/' . $this->vertex1Array['_key'],
                $this->v1 . '/' . $this->vertex1Array['_key'],
                null,
                $edge1
            );
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);
        $this->createGraph();
        $this->graphHandler->saveEdge(
            $this->graphName,
            $this->v1 . '/' . $this->vertex1Array['_key'],
            $this->v4 . '/' . $this->vertex4Array['_key'],
            null,
            [],
            $this->e1
        );

        $ex = null;
        try {
            $this->graphHandler->getEdge($this->graphName, $this->edge1Array['_key']);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);

        $v1 = $this->graphHandler->getEdge($this->graphName, $this->edge1Array['_key'], [], $this->e1);
        $v2 = $this->graphHandler->getEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], []);

        static::assertEquals($v1->getInternalKey(), $v2->getInternalKey());

        $v1 = $this->graphHandler->getEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], []);
        $v  = Edge::createFromArray($this->edge1Array);
        $v->setRevision($v1->getRevision());
        $ex = null;
        try {
            $this->graphHandler->replaceEdge($this->graphName, $this->edge1Array['_key'], null, $v, []);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);

        $v1 = $this->graphHandler->getEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], []);
        $v  = Edge::createFromArray($this->edge1Array);
        $v->setFrom($v1->getFrom());
        $v->setTo($v1->getTo());
        $v->setRevision($v1->getRevision());
        static::assertTrue(
            $this->graphHandler->replaceEdge($this->graphName, $this->edge1Array['_key'], null, $v, ['revision' => $v1->getRevision()], $this->e1)
        );
        $v1 = $this->graphHandler->getEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], []);
        $v  = Edge::createFromArray($this->edge1Array);
        $v->setRevision($v1->getRevision());
        $v->setFrom($v1->getFrom());
        $v->setTo($v1->getTo());
        static::assertTrue(
            $this->graphHandler->replaceEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], null, $v, ['revision' => true])
        );
        $ex = null;
        try {
            $this->graphHandler->updateEdge($this->graphName, $this->edge1Array['_key'], null, $v, []);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);
        $v1 = $this->graphHandler->getEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], []);
        $v  = Edge::createFromArray($this->edge1Array);
        $v->setRevision($v1->getRevision());
        static::assertTrue(
            $this->graphHandler->updateEdge($this->graphName, $this->edge1Array['_key'], null, $v, ['revision' => true], $this->e1)
        );
        $v1 = $this->graphHandler->getEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], []);
        $v  = Edge::createFromArray($this->edge1Array);
        $v->setRevision($v1->getRevision());
        static::assertTrue(
            $this->graphHandler->updateEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], null, $v, ['revision' => $v1->getRevision()])
        );
        //removeVertex($graph, $vertexId, $revision = null, $options = [], $collection = null)
        $ex = null;
        try {
            $this->graphHandler->removeEdge($this->graphName, $this->edge1Array['_key'], null, []);
        } catch (Exception $e) {
            $ex = $e->getMessage();
        }
        static::assertEquals('A collection must be provided.', $ex);
        $v1 = $this->graphHandler->getVertex($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], []);
        static::assertTrue($this->graphHandler->removeEdge($this->graphName, $this->e1 . '/' . $this->edge1Array['_key'], $v1->getRevision(), []));


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
