<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use ArangoDBClient\ClientException as ArangoClientException;
use ArangoDBClient\Collection as ArangoCollection;
use ArangoDBClient\CollectionHandler as ArangoCollectionHandler;
use ArangoDBClient\ConnectException as ArangoConnectException;
use ArangoDBClient\Connection as ArangoConnection;
use ArangoDBClient\ConnectionOptions as ArangoConnectionOptions;
use ArangoDBClient\DocumentHandler as ArangoDocumentHandler;
use ArangoDBClient\Exception as ArangoException;
use ArangoDBClient\Export as ArangoExport;
use ArangoDBClient\ServerException as ArangoServerException;
use ArangoDBClient\UpdatePolicy as ArangoUpdatePolicy;

/**
 * ArangoDb service, use arangodb no sql database
 * more information : https://www.arangodb.com
 * php client : https://github.com/arangodb/arangodb-php
 *
 * Pi::service('arangoDb')->insert($params, $collection);
 * Pi::service('arangoDb')->find($id, $collection);
 * Pi::service('arangoDb')->select($params, $collection);
 * Pi::service('arangoDb')->update($id, $params, $collection);
 * Pi::service('arangoDb')->delete($id, $collection);
 * Pi::service('arangoDb')->export($params, $collection);
 *
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 */
class ArangoDb extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'arangodb';

    protected function connection()
    {
        $connectionOptions = [
            // database name
            ArangoConnectionOptions::OPTION_DATABASE      => $this->options['database'],

            // server endpoint to connect to
            ArangoConnectionOptions::OPTION_ENDPOINT      => $this->options['endpoint'],

            // authorization type to use (currently supported: 'Basic')
            ArangoConnectionOptions::OPTION_AUTH_TYPE     => $this->options['authorization_type'],

            // user for basic authorization
            ArangoConnectionOptions::OPTION_AUTH_USER     => $this->options['user'],

            // password for basic authorization
            ArangoConnectionOptions::OPTION_AUTH_PASSWD   => $this->options['password'],

            // connection persistence on server. can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
            ArangoConnectionOptions::OPTION_CONNECTION    => $this->options['connection'],

            // connect timeout in seconds
            ArangoConnectionOptions::OPTION_TIMEOUT       => $this->options['timeout'],

            // whether or not to reconnect when a keep-alive connection has timed out on server
            ArangoConnectionOptions::OPTION_RECONNECT     => $this->options['reconnect'],

            // optionally create new collections when inserting documents
            ArangoConnectionOptions::OPTION_CREATE        => $this->options['create'],

            // optionally create new collections when inserting documents
            ArangoConnectionOptions::OPTION_UPDATE_POLICY => ArangoUpdatePolicy::LAST,
        ];

        // turn on exception logging (logs to whatever PHP is configured)
        ArangoException::enableLogging();

        // Set connection
        return new ArangoConnection($connectionOptions);
    }

    protected function check($connection, $collection = 'logs')
    {
        // Set collection handler
        $collectionHandler = new ArangoCollectionHandler($connection);

        // Check collection
        $result = $collectionHandler->has($collection);

        // Check collection and Create a new collection if not exist
        if (!$result && $this->options['forceCreate']) {
            $newCollection = new ArangoCollection();
            $newCollection->setName($collection);
            $collectionHandler->create($newCollection);

            $result = $collectionHandler->has($collection);
        }

        // Check collection
        return $result;
    }

    public function insert($params, $collection = 'logs')
    {
        // Check ArangoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $connection = $this->connection();

            // Make check
            if (!$this->check($connection, $collection)) {
                return [
                    'status'  => 0,
                    'message' => __('Error , select collection not exist, please generate collection before make any type of query'),
                ];
            } else {
                // Try save log
                try {
                    // Set document handler
                    $handler = new ArangoDocumentHandler($connection);

                    // send the document to the server and git id
                    $result = $handler->save($collection, $params);

                    // Return result
                    return [
                        'status' => 1,
                        'result' => $result,
                    ];
                } catch (ArangoConnectException $e) {
                    return 'Connection error: ' . $e->getMessage();
                } catch (ArangoClientException $e) {
                    return 'Client error: ' . $e->getMessage();
                } catch (ArangoServerException $e) {
                    return 'Server error: ' . $e->getServerCode() . ':' . $e->getServerMessage() . ' ' . $e->getMessage();
                }
            }
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, ArangoDB not active, please setup database and update service.arangodb.php config file'),
            ];
        }
    }

    public function find($id, $collection = 'logs')
    {
        // Check ArangoDB setup and active or not
        if ($this->options['active'] && !empty($id)) {

            // Get connection option
            $connection = $this->connection();

            // Make check
            if (!$this->check($connection, $collection)) {
                return [
                    'status'  => 0,
                    'message' => __('Error , select collection not exist, please generate collection before make any type of query'),
                ];
            } else {
                try {
                    // Set document handler
                    $handler = new ArangoDocumentHandler($connection);

                    // get the document back from the server
                    $result = $handler->get($collection, $id);

                    // Return result
                    return [
                        'status' => 1,
                        'result' => $result,
                    ];
                } catch (ArangoConnectException $e) {
                    return 'Connection error: ' . $e->getMessage();
                } catch (ArangoClientException $e) {
                    return 'Client error: ' . $e->getMessage();
                } catch (ArangoServerException $e) {
                    return 'Server error: ' . $e->getServerCode() . ':' . $e->getServerMessage() . ' ' . $e->getMessage();
                }
            }
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, ArangoDB not active, please setup database and update service.arangodb.php config file'),
            ];
        }
    }

    public function select($params, $collection = 'logs')
    {
        // Check ArangoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $connection = $this->connection();

            // Make check
            if (!$this->check($connection, $collection)) {
                return [
                    'status'  => 0,
                    'message' => __('Error , select collection not exist, please generate collection before make any type of query'),
                ];
            } else {
                try {
                    // Set collection handler
                    $collectionHandler = new ArangoCollectionHandler($connection);

                    // get a document list back from the server, using a document example
                    $cursor = $collectionHandler->byExample($collection, $params);

                    // Return result
                    return [
                        'status' => 1,
                        'result' => $cursor->getAll(),
                    ];
                } catch (ArangoConnectException $e) {
                    return 'Connection error: ' . $e->getMessage();
                } catch (ArangoClientException $e) {
                    return 'Client error: ' . $e->getMessage();
                } catch (ArangoServerException $e) {
                    return 'Server error: ' . $e->getServerCode() . ':' . $e->getServerMessage() . ' ' . $e->getMessage();
                }
            }
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, ArangoDB not active, please setup database and update service.arangodb.php config file'),
            ];
        }
    }

    public function update($id, $params, $collection = 'logs')
    {
        // Check ArangoDB setup and active or not
        if ($this->options['active'] && !empty($id) && !empty($params)) {
            // Get connection option
            $connection = $this->connection();

            // Make check
            if (!$this->check($connection, $collection)) {
                return [
                    'status'  => 0,
                    'message' => __('Error , select collection not exist, please generate collection before make any type of query'),
                ];
            } else {
                try {
                    // Set document handler
                    $handler = new ArangoDocumentHandler($connection);

                    // get the document back from the server
                    $object = $handler->get($collection, $id);

                    // ToDo : update params

                    // Update Document
                    $handler->update($object);

                    // Get updated document
                    $result = $handler->get($collection, $id);

                    // Return result
                    return [
                        'status' => 1,
                        'result' => $result,
                    ];
                } catch (ArangoConnectException $e) {
                    return 'Connection error: ' . $e->getMessage();
                } catch (ArangoClientException $e) {
                    return 'Client error: ' . $e->getMessage();
                } catch (ArangoServerException $e) {
                    return 'Server error: ' . $e->getServerCode() . ':' . $e->getServerMessage() . ' ' . $e->getMessage();
                }
            }
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, ArangoDB not active, please setup database and update service.arangodb.php config file'),
            ];
        }
    }

    public function delete($id, $collection = 'logs')
    {
        // Check ArangoDB setup and active or not
        if ($this->options['active'] && !empty($id)) {
            // Get connection option
            $connection = $this->connection();

            // Make check
            if (!$this->check($connection, $collection)) {
                return [
                    'status'  => 0,
                    'message' => __('Error , select collection not exist, please generate collection before make any type of query'),
                ];
            } else {
                try {
                    // Set document handler
                    $handler = new ArangoDocumentHandler($connection);

                    // Get the document back from the server
                    $object = $handler->get($collection, $id);

                    // Remove object
                    $result = $handler->remove($object);

                    // Return result
                    return [
                        'status' => 1,
                        'result' => $result,
                    ];
                } catch (ArangoConnectException $e) {
                    return 'Connection error: ' . $e->getMessage();
                } catch (ArangoClientException $e) {
                    return 'Client error: ' . $e->getMessage();
                } catch (ArangoServerException $e) {
                    return 'Server error: ' . $e->getServerCode() . ':' . $e->getServerMessage() . ' ' . $e->getMessage();
                }
            }
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, ArangoDB not active, please setup database and update service.arangodb.php config file'),
            ];
        }
    }

    public function export($params, $collection = 'logs')
    {
        // Check ArangoDB setup and active or not
        if ($this->options['active']) {
            // Get connection option
            $connection = $this->connection();

            // Make check
            if (!$this->check($connection, $collection)) {
                return [
                    'status'  => 0,
                    'message' => __('Error , select collection not exist, please generate collection before make any type of query'),
                ];
            } else {
                try {
                    // creates an export object for collection users
                    $export = new ArangoExport($connection, $collection, $params);

                    // execute the export. this will return a special, forward-only cursor
                    $cursor = $export->execute();

                    // now we can fetch the documents from the collection in blocks
                    $result = [];
                    while ($docs = $cursor->getNextBatch()) {
                        $result[] = $docs;
                    }

                    // Return result
                    return [
                        'status' => 1,
                        'result' => $result,
                    ];
                } catch (ArangoConnectException $e) {
                    return 'Connection error: ' . $e->getMessage();
                } catch (ArangoClientException $e) {
                    return 'Client error: ' . $e->getMessage();
                } catch (ArangoServerException $e) {
                    return 'Server error: ' . $e->getServerCode() . ':' . $e->getServerMessage() . ' ' . $e->getMessage();
                }
            }
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, ArangoDB not active, please setup database and update service.arangodb.php config file'),
            ];
        }
    }
}
