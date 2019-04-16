<?php

namespace ArangoDBClient;

// get connection options from a helper file
require __DIR__ . '/init.php';


try {
    // Setup connection, graph and graph handler
    $connection   = new Connection($connectionOptions);
    $graphHandler = new GraphHandler($connection);
    $graph        = new Graph();
    $graph->set('_key', 'Graph');
    $graph->addEdgeDefinition(EdgeDefinition::createUndirectedRelation('EdgeCollection', 'VertexCollection'));

    try {
        $graphHandler->dropGraph($graph);
    } catch (\Exception $e) {
        // graph may not yet exist. ignore this error for now
    }

    $graphHandler->createGraph($graph);

    // Define some arrays to build the content of the vertices and edges
    $vertex1Array = [
        '_key'     => 'vertex1',
        'someKey1' => 'someValue1'
    ];
    $vertex2Array = [
        '_key'     => 'vertex2',
        'someKey2' => 'someValue2'
    ];
    $edge1Array   = [
        '_key'         => 'edge1',
        'someEdgeKey1' => 'someEdgeValue1'
    ];

    // Create documents for 2 vertices and a connecting edge
    $vertex1 = Vertex::createFromArray($vertex1Array);
    $vertex2 = Vertex::createFromArray($vertex2Array);
    $edge1   = Edge::createFromArray($edge1Array);

    // Save the vertices
    $graphHandler->saveVertex('Graph', $vertex1);
    $graphHandler->saveVertex('Graph', $vertex2);

    // Get the vertices
    $graphHandler->getVertex('Graph', 'vertex1');
    $graphHandler->getVertex('Graph', 'vertex2');

    // check if vertex exists
    var_dump($graphHandler->hasVertex('Graph', 'vertex1'));

    // Save the connecting edge
    $graphHandler->saveEdge('Graph', $vertex1->getHandle(), $vertex2->getHandle(), 'somelabelValue', $edge1);

    // check if edge exists
    var_dump($graphHandler->hasEdge('Graph', 'edge1'));

    // Get the connecting edge
    $graphHandler->getEdge('Graph', 'edge1');

    // Remove vertices and edges
    $graphHandler->removeVertex('Graph', 'vertex1');
    $graphHandler->removeVertex('Graph', 'vertex2');

    // the connecting edge will be deleted automatically
} catch (ConnectException $e) {
    print $e . PHP_EOL;
} catch (ServerException $e) {
    print $e . PHP_EOL;
} catch (ClientException $e) {
    print $e . PHP_EOL;
}
