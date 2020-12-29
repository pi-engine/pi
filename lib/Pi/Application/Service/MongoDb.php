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

use MongoDB\Client;
use MongoDB\BSON\Regex;

/**
 * MongoDb service, use mongodb no sql database
 * more information : https://www.mongodb.com
 * php client source : https://github.com/mongodb/mongo-php-library
 * php client doc : https://docs.mongodb.com/php-library
 * CRUD : https://docs.mongodb.com/php-library/current/tutorial/crud
 *
 * Pi::service('mongoDb')->insertOne($params, $collection);
 * Pi::service('mongoDb')->insertMany($params, $collection);
 * Pi::service('mongoDb')->findOne($params, $collection);
 * Pi::service('mongoDb')->findMany($params, $collection);
 * Pi::service('mongoDb')->aggregate($params, $collection);
 * Pi::service('mongoDb')->updateOne($params, $collection);
 * Pi::service('mongoDb')->updateMany($params, $collection);
 * Pi::service('mongoDb')->replaceOne($params, $collection);
 * Pi::service('mongoDb')->deleteOne($params, $collection);
 * Pi::service('mongoDb')->deleteMany($params, $collection);
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class MongoDb extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'mongodb';

    protected function connection()
    {
        // Set database
        $db = $this->options['database'];

        // Make connection
        $client = new Client($this->options['uri']);

        return $client->$db;
    }

    /*
     * Insert One Document
     */
    public function insertOne($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client          = $this->connection();
            $client          = $client->$collection;
            $insertOneResult = $client->insertOne($params);

            // Return result
            return [
                'status' => 1,
                'id'     => $insertOneResult->getInsertedId(),
                'count'  => $insertOneResult->getInsertedCount(),
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Insert Many Documents
     */
    public function insertMany($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client           = $this->connection();
            $client           = $client->$collection;
            $insertManyResult = $client->insertMany($params);

            // Return result
            return [
                'status' => 1,
                'ids'    => $insertManyResult->getInsertedIds(),
                'count'  => $insertManyResult->getInsertedCount(),
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Find One Document
    */
    public function findOne($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client   = $this->connection();
            $client   = $client->$collection;
            $document = $client->findOne($params);

            // Return result
            return [
                'status'   => 1,
                'document' => $document,
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Find Many Documents
     * Query Projection
     * Limit, Sort, and Skip Options
     * Regular Expressions
     */
    public function findMany($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client = $this->connection();
            $client = $client->$collection;
            $cursor = $client->find($params);

            $documents = [];
            foreach ($cursor as $document) {
                $documents[$document['_id']] = $document;
            }

            // Return result
            return [
                'status'   => 1,
                'document' => $documents,
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Complex Queries with Aggregation
     */
    public function aggregate($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client = $this->connection();
            $client = $client->$collection;
            $cursor = $client->aggregate($params);

            $documents = [];
            foreach ($cursor as $document) {
                $documents[$document['_id']] = $document;
            }

            // Return result
            return [
                'status'   => 1,
                'document' => $documents,
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Update One Document
     */
    public function updateOne($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client       = $this->connection();
            $client       = $client->$collection;
            $updateResult = $client->updateOne($params);


            // Return result
            return [
                'status'        => 1,
                'matchedCount'  => $updateResult->getMatchedCount(),
                'modifiedCount' => $updateResult->getModifiedCount(),
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Update Many Documents
     */
    public function updateMany($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client       = $this->connection();
            $client       = $client->$collection;
            $updateResult = $client->updateMany($params);


            // Return result
            return [
                'status'        => 1,
                'matchedCount'  => $updateResult->getMatchedCount(),
                'modifiedCount' => $updateResult->getModifiedCount(),
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Replace Documents
     */
    public function replaceOne($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client       = $this->connection();
            $client       = $client->$collection;
            $updateResult = $client->replaceOne($params);


            // Return result
            return [
                'status'        => 1,
                'matchedCount'  => $updateResult->getMatchedCount(),
                'modifiedCount' => $updateResult->getModifiedCount(),
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Delete One Document
     */
    public function deleteOne($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client       = $this->connection();
            $client       = $client->$collection;
            $deleteResult = $client->deleteOne($params);


            // Return result
            return [
                'status'       => 1,
                'deletedCount' => $deleteResult->getDeletedCount(),
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }

    /*
     * Delete Many Documents
     */
    public function deleteMany($params, $collection = 'logs')
    {
        // Check MongoDB setup and active or not
        if ($this->options['active'] && !empty($params)) {

            // Get connection option
            $client       = $this->connection();
            $client       = $client->$collection;
            $deleteResult = $client->deleteMany($params);


            // Return result
            return [
                'status'       => 1,
                'deletedCount' => $deleteResult->getDeletedCount(),
            ];
        } else {
            return [
                'status'  => 0,
                'message' => __('Error, MongoDB not active, please setup database and update service.mongodb.php config file'),
            ];
        }
    }
}
