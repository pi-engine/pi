<?php

namespace ArangoDBClient;

require __DIR__ . '/init.php';

try {
    $connection = new Connection($connectionOptions);

    // creates an export object for collection 'users'
    $export = new Export($connection, 'users', [
            'batchSize' => 5000,
            '_flat'     => true,
            'flush'     => true,
            'restrict'  => [
                'type'   => 'include',
                'fields' => ['_key', '_rev']
            ]
        ]
    );

    // execute the export. this will return a special, forward-only cursor
    $cursor = $export->execute();

    // now we can fetch the documents from the collection in blocks
    while ($docs = $cursor->getNextBatch()) {
        // do something with $docs
        print sprintf('retrieved %d documents', count($docs)) . PHP_EOL;
    }

} catch (ConnectException $e) {
    print $e . PHP_EOL;
} catch (ServerException $e) {
    print $e . PHP_EOL;
} catch (ClientException $e) {
    print $e . PHP_EOL;
}
