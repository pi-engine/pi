<?php

/**
 * ArangoDB PHP client: transaction
 *
 * @package   ArangoDBClient
 * @author    Frank Mayer
 * @copyright Copyright 2013, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Transaction object
 *
 * A transaction is an object that is used to prepare and send a transaction
 * to the server.
 *
 * The object encapsulates:<br />
 * <ul>
 * <li> the collections definitions for locking
 * <li> the actual javascript function
 * <li> additional options like waitForSync, lockTimeout and params
 * </ul>
 *
 * The transaction object requires the connection object and can be initialized
 * with or without initial transaction configuration.
 * Any configuration can be set and retrieved by the object's methods like this:<br />
 *
 * <pre>
 * $this->setAction('function (){your code};');
 * $this->setCollections(array('read' => 'my_read_collection, 'write' => array('col_1', 'col2')));
 * </pre>
 * <br />
 * or like this:
 * <pre>
 * $this->action('function (){your code};');
 * $this->collections(array('read' => 'my_read_collection, 'write' => array('col_1', 'col2')));
 * </pre>
 * <br />
 * There are also helper functions to set collections directly, based on their locking:
 * <pre>
 * $this->setWriteCollections($array or $string if single collection)
 * $this->setReadCollections($array or $string if single collection)
 * </pre>
 * <br />
 *
 * @property array  $collection      - The collections array that includes both read and write collection definitions
 * @property mixed  $readCollection  - The read-collections array or string (if only one)
 * @property mixed  $writeCollection - The write-collections array or string (if only one)
 * @property string $action          - The action to pass to the server
 * @property bool   $waitForSync     - WaitForSync on the transaction
 * @property int    $lockTimeout     - LockTimeout on the transaction
 *
 * @package ArangoDBClient
 * @since   1.3
 */
class Transaction
{
    /**
     * The connection object
     *
     * @var Connection
     */
    private $_connection;

    /**
     * The transaction's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Collections index
     */
    const ENTRY_COLLECTIONS = 'collections';

    /**
     * Action index
     */
    const ENTRY_ACTION = 'action';

    /**
     * WaitForSync index
     */
    const ENTRY_WAIT_FOR_SYNC = 'waitForSync';

    /**
     * Lock timeout index
     */
    const ENTRY_LOCK_TIMEOUT = 'lockTimeout';

    /**
     * Params index
     */
    const ENTRY_PARAMS = 'params';

    /**
     * Read index
     */
    const ENTRY_READ = 'read';

    /**
     * WRITE index
     */
    const ENTRY_WRITE = 'write';

    /**
     * @var $_action string The action property of the transaction.
     */
    protected $_action;

    /**
     * Initialise the transaction object
     *
     * The $transaction array can be used to specify the collections, action and further
     * options for the transaction in form of an array.
     *
     * Example:
     * array(
     *   'collections' => array(
     *     'write' => array(
     *       'my_collection'
     *      )
     *    ),
     *   'action' => 'function (){}',
     *   'waitForSync' => true
     * )
     *
     *
     * @param Connection $connection       - the connection to be used
     * @param array      $transactionArray - transaction initialization data
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function __construct(Connection $connection, array $transactionArray = null)
    {
        $this->_connection = $connection;
        if (is_array($transactionArray)) {
            $this->buildTransactionAttributesFromArray($transactionArray);
        }
    }


    /**
     * Execute the transaction
     *
     * This will post the query to the server and return the results as
     * a Cursor. The cursor can then be used to iterate the results.
     *
     * @throws Exception throw exception if transaction failed
     * @return mixed true if successful without a return value or the return value if one was set in the action
     */
    public function execute()
    {
        $response      = $this->_connection->post(
            Urls::URL_TRANSACTION,
            $this->getConnection()->json_encode_wrapper($this->attributes)
        );
        $responseArray = $response->getJson();
        if (isset($responseArray['result'])) {
            return $responseArray['result'];
        }

        return true;
    }


    /**
     * Return the connection object
     *
     * @return Connection - the connection object
     */
    protected function getConnection()
    {
        return $this->_connection;
    }


    /**
     * Set the collections array.
     *
     * The array should have 2 sub-arrays, namely 'read' and 'write' which should hold the respective collections
     * for the transaction
     *
     * @param array $value
     */
    public function setCollections(array $value)
    {
        if (array_key_exists('read', $value)) {
            $this->setReadCollections($value['read']);
        }
        if (array_key_exists('write', $value)) {
            $this->setWriteCollections($value['write']);
        }
    }


    /**
     * Get collections array
     *
     * This holds the read and write collections of the transaction
     *
     * @return array $value
     */
    public function getCollections()
    {
        return $this->get(self::ENTRY_COLLECTIONS);
    }


    /**
     * set action value
     *
     * @param string $value
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function setAction($value)
    {
        $this->set(self::ENTRY_ACTION, (string) $value);
    }


    /**
     * get action value
     *
     * @return string action
     */
    public function getAction()
    {
        return $this->get(self::ENTRY_ACTION);
    }


    /**
     * set waitForSync value
     *
     * @param bool $value
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function setWaitForSync($value)
    {
        $this->set(self::ENTRY_WAIT_FOR_SYNC, (bool) $value);
    }


    /**
     * get waitForSync value
     *
     * @return bool waitForSync
     */
    public function getWaitForSync()
    {
        return $this->get(self::ENTRY_WAIT_FOR_SYNC);
    }


    /**
     * Set lockTimeout value
     *
     * @param int $value
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function setLockTimeout($value)
    {
        $this->set(self::ENTRY_LOCK_TIMEOUT, (int) $value);
    }


    /**
     * Get lockTimeout value
     *
     * @return int lockTimeout
     */
    public function getLockTimeout()
    {
        return $this->get(self::ENTRY_LOCK_TIMEOUT);
    }


    /**
     * Set params value
     *
     * @param array $value
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function setParams(array $value)
    {
        $this->set(self::ENTRY_PARAMS, (array) $value);
    }


    /**
     * Get params value
     *
     * @return array params
     */
    public function getParams()
    {
        return $this->get(self::ENTRY_PARAMS);
    }


    /**
     * Convenience function to directly set write-collections without having to access
     * them from the collections attribute.
     *
     * @param array $value
     */
    public function setWriteCollections($value)
    {

        $this->attributes[self::ENTRY_COLLECTIONS][self::ENTRY_WRITE] = $value;
    }


    /**
     * Convenience function to directly get write-collections without having to access
     * them from the collections attribute.
     *
     * @return array params
     */
    public function getWriteCollections()
    {
        return $this->attributes[self::ENTRY_COLLECTIONS][self::ENTRY_WRITE];
    }


    /**
     * Convenience function to directly set read-collections without having to access
     * them from the collections attribute.
     *
     * @param array $value
     */
    public function setReadCollections($value)
    {

        $this->attributes[self::ENTRY_COLLECTIONS][self::ENTRY_READ] = $value;
    }


    /**
     * Convenience function to directly get read-collections without having to access
     * them from the collections attribute.
     *
     * @return array params
     */
    public function getReadCollections()
    {
        return $this->attributes[self::ENTRY_COLLECTIONS][self::ENTRY_READ];
    }


    /**
     * Sets an attribute
     *
     * @param $key
     * @param $value
     *
     * @throws ClientException
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new ClientException('Invalid document attribute key');
        }

        $this->attributes[$key] = $value;
    }


    /**
     * Set an attribute, magic method
     *
     * This is a magic method that allows the object to be used without
     * declaring all document attributes first.
     *
     * @throws ClientException
     *
     * @magic
     *
     * @param string $key   - attribute name
     * @param mixed  $value - value for attribute
     *
     * @return void
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case self::ENTRY_COLLECTIONS :
                $this->setCollections($value);
                break;
            case 'writeCollections' :
                $this->setWriteCollections($value);
                break;
            case 'readCollections' :
                $this->setReadCollections($value);
                break;
            case self::ENTRY_ACTION :
                $this->setAction($value);
                break;
            case self::ENTRY_WAIT_FOR_SYNC :
                $this->setWaitForSync($value);
                break;
            case self::ENTRY_LOCK_TIMEOUT :
                $this->setLockTimeout($value);
                break;
            case self::ENTRY_PARAMS :
                $this->setParams($value);
                break;
            default:
                $this->set($key, $value);
                break;
        }
    }


    /**
     * Get an attribute
     *
     * @param string $key - name of attribute
     *
     * @return mixed - value of attribute, NULL if attribute is not set
     */
    public function get($key)
    {
        switch ($key) {
            case 'writeCollections' :
                return $this->getWriteCollections();
                break;
            case 'readCollections' :
                return $this->getReadCollections();
                break;
        }

        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return null;
    }


    /**
     * Get an attribute, magic method
     *
     * This function is mapped to get() internally.
     *
     * @magic
     *
     * @param string $key - name of attribute
     *
     * @return mixed - value of attribute, NULL if attribute is not set
     */
    public function __get($key)
    {
        return $this->get($key);
    }


    /**
     * Is triggered by calling isset() or empty() on inaccessible properties.
     *
     * @param string $key - name of attribute
     *
     * @return boolean returns true or false (set or not set)
     */
    public function __isset($key)
    {
        if (isset($this->attributes[$key])) {
            return true;
        }

        return false;
    }


    /**
     * Returns the action string
     *
     * @magic
     *
     * @return string - the current action string
     */
    public function __toString()
    {
        return $this->_action;
    }


    /**
     * Build the object's attributes from a given array
     *
     * @param $options
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function buildTransactionAttributesFromArray($options)
    {
        if (isset($options[self::ENTRY_COLLECTIONS])) {
            $this->setCollections($options[self::ENTRY_COLLECTIONS]);
        }

        if (isset($options[self::ENTRY_ACTION])) {
            $this->setAction($options[self::ENTRY_ACTION]);
        }

        if (isset($options[self::ENTRY_WAIT_FOR_SYNC])) {
            $this->setWaitForSync($options[self::ENTRY_WAIT_FOR_SYNC]);
        }

        if (isset($options[self::ENTRY_LOCK_TIMEOUT])) {
            $this->setLockTimeout($options[self::ENTRY_LOCK_TIMEOUT]);
        }

        if (isset($options[self::ENTRY_PARAMS])) {
            $this->setParams($options[self::ENTRY_PARAMS]);
        }
    }
}

class_alias(Transaction::class, '\triagens\ArangoDb\Transaction');
