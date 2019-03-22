<?php

namespace ArangoDBClient;

require __DIR__ . '/init.php';

try {
    $connection        = new Connection($connectionOptions);
    $collectionHandler = new CollectionHandler($connection);
    $handler           = new DocumentHandler($connection);

    // set up a document collection "users"
    $collection = new Collection('users');
    try {
        $collectionHandler->create($collection);
    } catch (\Exception $e) {
        // collection may already exist - ignore this error for now
    }

    // create a new document
    $user = new Document();
    $user->set('name', 'John');
    $user->age = 19;

    $id = $handler->save('users', $user);

    // get documents by example
    $cursor = $collectionHandler->byExample('users', ['name' => 'John', 'age' => 19]);
    var_dump($cursor->getAll());

    // get the ids of all documents in the collection
    $result = $collectionHandler->getAllIds('users');
    var_dump($result);

    // create another new document
    $user = new Document();
    $user->set('name', 'j-lo');
    $user->level = 1;
    $user->vists = [1, 2, 3];

    $id = $handler->save('users', $user);
    var_dump('CREATED A NEW DOCUMENT WITH ID: ', $id);

    // get this document from the server
    $userFromServer = $handler->getById('users', $id);
    var_dump($userFromServer);

    // update this document
    $userFromServer->nonsense = 'hihi';
    unset($userFromServer->name);
    $result = $handler->update($userFromServer);
    var_dump($result);

    // get the updated document back
    $result = $handler->get('users', $id);
    var_dump($result);

    // delete the document
    $result = $handler->removeById('users', $id);
    var_dump($result);

    // check if a document exists
    $result = $handler->has('users', 'foobar123');
    var_dump($result);
} catch (ConnectException $e) {
    print $e . PHP_EOL;
} catch (ServerException $e) {
    print $e . PHP_EOL;
} catch (ClientException $e) {
    print $e . PHP_EOL;
}
