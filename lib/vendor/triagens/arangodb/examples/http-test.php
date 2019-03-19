<?php

namespace ArangoDBClient;

require __DIR__ . '/init.php';

$n = 100 * 1000; // number of documents

try {
    // turn off tracing... it's too verbose here
    unset($connectionOptions[ConnectionOptions::OPTION_TRACE]);

    $connection        = new Connection($connectionOptions);
    $collectionHandler = new CollectionHandler($connection);
    $handler           = new DocumentHandler($connection);

    // set up a document collection "test"
    // first try to remove it if it already exists
    try {
        $collectionHandler->drop('test');
    } catch (\Exception $e) {
        // collection may not exist. we don't care here
    }

    // now create the collection
    $collection = new Collection('test');
    $collectionHandler->create($collection);

    echo "creating $n documents" . PHP_EOL;
    $time = microtime(true);

    // create lots of documents sequentially
    // this issues lots of HTTP requests to the server so we
    // can test the HTTP layer
    for ($i = 0; $i < $n; ++$i) {
        $document = new Document(['value' => 'test' . $i]);

        $handler->save('test', $document);
    }

    echo 'creating documents took ' . (microtime(true) - $time) . ' s' . PHP_EOL;

} catch (ConnectException $e) {
    print $e . PHP_EOL;
} catch (ServerException $e) {
    print $e . PHP_EOL;
} catch (ClientException $e) {
    print $e . PHP_EOL;
}
