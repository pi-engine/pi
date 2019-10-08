<?php

namespace ArangoDBClient;

require __DIR__ . '/init.php';

try {
    $connection = new Connection($connectionOptions);
    $handler    = new CollectionHandler($connection);

    // create a new collection
    $col = new Collection();
    $col->setName('hihi');
    $result = $handler->create($col);
    var_dump($result);

    // check if a collection exists
    $result = $handler->has('foobar');
    var_dump($result);

    // get an existing collection
    $result = $handler->get('hihi');
    var_dump($result);

    // get an existing collection
    $result = $handler->get('hihi');
    var_dump($result);

    // get number of documents from an existing collection
    $result = $handler->count('hihi');
    var_dump($result);

    // get figures for an existing collection
    $result = $handler->figures('hihi');
    var_dump($result);

    // delete the collection
    $result = $handler->drop('hihi');
    var_dump($result);
    // rename a collection
    // $handler->rename($col, "hihi30");

    // truncate an existing collection
    // $result = $handler->truncate("hihi");
} catch (ConnectException $e) {
    print $e . PHP_EOL;
} catch (ServerException $e) {
    print $e . PHP_EOL;
} catch (ClientException $e) {
    print $e . PHP_EOL;
}
