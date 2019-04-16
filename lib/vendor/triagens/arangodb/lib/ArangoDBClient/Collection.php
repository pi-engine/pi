<?php

/**
 * ArangoDB PHP client: single collection
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Value object representing a collection
 *
 * <br>
 *
 * @package   ArangoDBClient
 * @since     0.2
 */
class Collection
{
    /**
     * The collection id (might be NULL for new collections)
     *
     * @var mixed - collection id
     */
    private $_id;

    /**
     * The collection name (might be NULL for new collections)
     *
     * @var string - collection name
     */
    private $_name;

    /**
     * The collection type (might be NULL for new collections)
     *
     * @var int - collection type
     */
    private $_type;

    /**
     * The collection waitForSync value (might be NULL for new collections)
     *
     * @var bool - waitForSync value
     */
    private $_waitForSync;

    /**
     * The collection journalSize value (might be NULL for new collections)
     *
     * @var int - journalSize value
     */
    private $_journalSize;

    /**
     * The collection isSystem value (might be NULL for new collections)
     *
     * @var bool - isSystem value
     */
    private $_isSystem;

    /**
     * The collection isVolatile value (might be NULL for new collections)
     *
     * @var bool - isVolatile value
     */
    private $_isVolatile;

    /**
     * The collection numberOfShards value (might be NULL for new collections)
     *
     * @var mixed - numberOfShards value
     */
    private $_numberOfShards;
    
    /**
     * The replicationFactor value (might be NULL for new collections)
     *
     * @var mixed - replicationFactor value
     */
    private $_replicationFactor;
    
    /**
     * The shardingStrategy value (might be NULL for new collections)
     *
     * @var mixed - shardingStrategy value
     */
    private $_shardingStrategy;

    /**
     * The collection shardKeys value (might be NULL for new collections)
     *
     * @var array - shardKeys value
     */
    private $_shardKeys;

    /**
     * The collection status value
     *
     * @var int - status value
     */
    private $_status;

    /**
     * The collection keyOptions value
     *
     * @var array - keyOptions value
     */
    private $_keyOptions;

    /**
     * Collection id index
     */
    const ENTRY_ID = 'id';

    /**
     * Collection name index
     */
    const ENTRY_NAME = 'name';

    /**
     * Collection type index
     */
    const ENTRY_TYPE = 'type';

    /**
     * Collection 'waitForSync' index
     */
    const ENTRY_WAIT_SYNC = 'waitForSync';

    /**
     * Collection 'journalSize' index
     */
    const ENTRY_JOURNAL_SIZE = 'journalSize';

    /**
     * Collection 'status' index
     */
    const ENTRY_STATUS = 'status';

    /**
     * Collection 'keyOptions' index
     */
    const ENTRY_KEY_OPTIONS = 'keyOptions';

    /**
     * Collection 'isSystem' index
     */
    const ENTRY_IS_SYSTEM = 'isSystem';

    /**
     * Collection 'isVolatile' index
     */
    const ENTRY_IS_VOLATILE = 'isVolatile';

    /**
     * Collection 'numberOfShards' index
     */
    const ENTRY_NUMBER_OF_SHARDS = 'numberOfShards';
    
    /**
     * Collection 'replicationFactor' index
     */
    const ENTRY_REPLICATION_FACTOR = 'replicationFactor';
    
    /**
     * Collection 'shardingStrategy' index
     */
    const ENTRY_SHARDING_STRATEGY = 'shardingStrategy';

    /**
     * Collection 'shardKeys' index
     */
    const ENTRY_SHARD_KEYS = 'shardKeys';

    /**
     * properties option
     */
    const OPTION_PROPERTIES = 'properties';

    /**
     * document collection type
     */
    const TYPE_DOCUMENT = 2;

    /**
     * edge collection type
     */
    const TYPE_EDGE = 3;

    /**
     * New born collection
     */
    const STATUS_NEW_BORN = 1;

    /**
     * Unloaded collection
     */
    const STATUS_UNLOADED = 2;

    /**
     * Loaded collection
     */
    const STATUS_LOADED = 3;

    /**
     * Collection being unloaded
     */
    const STATUS_BEING_UNLOADED = 4;

    /**
     * Deleted collection
     */
    const STATUS_DELETED = 5;

    /**
     * Constructs an empty collection
     *
     * @param string $name - name for the collection
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function __construct($name = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }
    }

    /**
     * Factory method to construct a new collection
     *
     * @throws ClientException
     *
     * @param array $values - initial values for collection
     *
     * @return Collection
     */
    public static function createFromArray(array $values)
    {
        $collection = new self();

        foreach ($values as $key => $value) {
            $collection->set($key, $value);
        }

        return $collection;
    }

    /**
     * Get the default collection type
     *
     * @return string - name
     */
    public static function getDefaultType()
    {
        return self::TYPE_DOCUMENT;
    }

    /**
     * Clone a collection
     *
     * Returns the clone
     *
     * @magic
     *
     * @return void
     */
    public function __clone()
    {
        $this->_id                = null;
        $this->_name              = null;
        $this->_waitForSync       = null;
        $this->_journalSize       = null;
        $this->_isSystem          = null;
        $this->_isVolatile        = null;
        $this->_numberOfShards    = null;
        $this->_replicationFactor = null;
        $this->_shardingStrategy  = null;
        $this->_shardKeys         = null;
    }

    /**
     * Get a string representation of the collection
     *
     * Returns the collection as JSON-encoded string
     *
     * @magic
     *
     * @return string - JSON-encoded collection
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Returns the collection as JSON-encoded string
     *
     * @return string - JSON-encoded collection
     */
    public function toJson()
    {
        return json_encode($this->getAll());
    }

    /**
     * Returns the collection as a serialized string
     *
     * @return string - PHP serialized collection
     */
    public function toSerialized()
    {
        return serialize($this->getAll());
    }

    /**
     * Get all collection attributes
     *
     * @return array - array of all collection attributes
     */
    public function getAll()
    {
        $result = [
            self::ENTRY_ID           => $this->_id,
            self::ENTRY_NAME         => $this->_name,
            self::ENTRY_WAIT_SYNC    => $this->_waitForSync,
            self::ENTRY_JOURNAL_SIZE => $this->_journalSize,
            self::ENTRY_IS_SYSTEM    => $this->_isSystem,
            self::ENTRY_IS_VOLATILE  => $this->_isVolatile,
            self::ENTRY_TYPE         => $this->_type,
            self::ENTRY_STATUS       => $this->_status,
            self::ENTRY_KEY_OPTIONS  => $this->_keyOptions
        ];

        if (null !== $this->_numberOfShards) {
            $result[self::ENTRY_NUMBER_OF_SHARDS] = $this->_numberOfShards;
        }
        
        if (null !== $this->_replicationFactor) {
            $result[self::ENTRY_REPLICATION_FACTOR] = $this->_replicationFactor;
        }
        
        if (null !== $this->_shardingStrategy) {
            $result[self::ENTRY_SHARDING_STRATEGY] = $this->_shardingStrategy;
        }

        if (is_array($this->_shardKeys)) {
            $result[self::ENTRY_SHARD_KEYS] = $this->_shardKeys;
        }

        return $result;
    }

    /**
     * Set a collection attribute
     *
     * The key (attribute name) must be a string.
     *
     * This will validate the value of the attribute and might throw an
     * exception if the value is invalid.
     *
     * @throws ClientException
     *
     * @param string $key   - attribute name
     * @param mixed  $value - value for attribute
     *
     * @return void
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new ClientException('Invalid collection attribute type');
        }

        if ($key === self::ENTRY_ID) {
            $this->setId($value);

            return;
        }

        if ($key === self::ENTRY_NAME) {
            $this->setName($value);

            return;
        }

        if ($key === self::ENTRY_WAIT_SYNC) {
            $this->setWaitForSync($value);

            return;
        }

        if ($key === self::ENTRY_JOURNAL_SIZE) {
            $this->setJournalSize($value);

            return;
        }

        if ($key === self::ENTRY_IS_SYSTEM) {
            $this->setIsSystem($value);

            return;
        }

        if ($key === self::ENTRY_IS_VOLATILE) {
            $this->setIsVolatile($value);

            return;
        }

        if ($key === self::ENTRY_TYPE) {
            $this->setType($value);

            return;
        }

        if ($key === self::ENTRY_STATUS) {
            $this->setStatus($value);

            return;
        }

        if ($key === self::ENTRY_KEY_OPTIONS) {
            $this->setKeyOptions($value);

            return;
        }

        if ($key === self::ENTRY_NUMBER_OF_SHARDS) {
            $this->setNumberOfShards($value);

            return;
        }
        
        if ($key === self::ENTRY_REPLICATION_FACTOR) {
            $this->setReplicationFactor($value);

            return;
        }
        
        if ($key === self::ENTRY_SHARDING_STRATEGY) {
            $this->setShardingStrategy($value);

            return;
        }

        if ($key === self::ENTRY_SHARD_KEYS) {
            $this->setShardKeys($value);

            return;
        }
        // unknown attribute, will be ignored
    }

    /**
     * Set the collection id
     *
     * This will throw if the id of an existing collection gets updated to some other id
     *
     * @throws ClientException
     *
     * @param mixed $id - collection id
     *
     * @return bool
     */
    public function setId($id)
    {
        if ($this->_id !== null && $this->_id !== $id) {
            throw new ClientException('Should not update the id of an existing collection');
        }

        return $this->_id = (string) $id;
    }

    /**
     * Get the collection id (if already known)
     *
     * Collection ids are generated on the server only.
     *
     * Collection ids are numeric but might be bigger than PHP_INT_MAX.
     * To reliably store a collection id elsewhere, a PHP string should be used
     *
     * @return mixed - collection id, might be NULL if collection does not yet have an id
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set the collection name
     *
     * @throws ClientException
     *
     * @param string $name - name
     *
     * @return void
     */
    public function setName($name)
    {
        assert(is_string($name));

        if ($this->_name !== null && $this->_name !== $name) {
            throw new ClientException('Should not update the name of an existing collection');
        }

        $this->_name = (string) $name;
    }

    /**
     * Get the collection name (if already known)
     *
     * @return string - name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set the collection type.
     *
     * This is useful before a collection is create() 'ed in order to set a different type than the normal one.
     * For example this must be set to 3 in order to create an edge-collection.
     *
     * @throws ClientException
     *
     * @param int $type - type = 2 -> normal collection, type = 3 -> edge-collection
     *
     * @return void
     */
    public function setType($type)
    {
        if ($type === 'document') {
            $type = self::TYPE_DOCUMENT;
        } else if ($type === 'edge') {
            $type = self::TYPE_EDGE;
        }

        if ($this->_type !== null && $this->_type !== $type) {
            throw new ClientException('Should not update the type of an existing collection');
        }

        if ($type !== self::TYPE_DOCUMENT && $type !== self::TYPE_EDGE) {
            throw new ClientException('Invalid type used for collection');
        }

        assert(is_int($type));
        $this->_type = $type;
    }

    /**
     * Get the collection type (if already known)
     *
     * @return string - name
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set the collection status.
     *
     * This is useful before a collection is create()'ed in order to set a status.
     *
     * @throws ClientException
     *
     * @param int $status - statuses = 1 -> new born, status = 2 -> unloaded, status = 3 -> loaded, status = 4 -> being unloaded, status = 5 -> deleted
     *
     * @return void
     */
    public function setStatus($status)
    {
        assert(is_int($status));

        if ($this->_status !== null && $this->_status !== $status) {
            throw new ClientException('Should not update the status of an existing collection');
        }

        if (!in_array(
            $status,
            [
                self::STATUS_NEW_BORN,
                self::STATUS_UNLOADED,
                self::STATUS_LOADED,
                self::STATUS_BEING_UNLOADED,
                self::STATUS_DELETED
            ], true
        )
        ) {
            throw new ClientException('Invalid status used for collection');
        }

        $this->_status = $status;
    }

    /**
     * Get the collection status (if already known)
     *
     * @return int - status
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Set the collection key options.
     *
     * @throws ClientException
     *
     * @param array $keyOptions - An associative array containing optional keys: type, allowUserKeys, increment, offset.
     *
     * @return void
     */
    public function setKeyOptions($keyOptions)
    {
        assert(is_array($keyOptions));

        $this->_keyOptions = $keyOptions;
    }

    /**
     * Get the collection key options (if already known)
     *
     * @return array - keyOptions
     */
    public function getKeyOptions()
    {
        return $this->_keyOptions;
    }

    /**
     * Set the waitForSync value
     *
     * @param bool $value - waitForSync value
     *
     * @return void
     */
    public function setWaitForSync($value)
    {
        assert(null === $value || is_bool($value));
        $this->_waitForSync = $value;
    }

    /**
     * Get the waitForSync value (if already known)
     *
     * @return bool - waitForSync value
     */
    public function getWaitForSync()
    {
        return $this->_waitForSync;
    }

    /**
     * Set the journalSize value
     *
     * @param int $value - journalSize value
     *
     * @return void
     */
    public function setJournalSize($value)
    {
        assert(is_numeric($value));
        $this->_journalSize = $value;
    }

    /**
     * Get the journalSize value (if already known)
     *
     * @return int - journalSize value
     */
    public function getJournalSize()
    {
        return $this->_journalSize;
    }

    /**
     * Set the isSystem value
     *
     * @param bool $value - isSystem: false->user collection, true->system collection
     *
     * @return void
     */
    public function setIsSystem($value)
    {
        assert(null === $value || is_bool($value));
        $this->_isSystem = $value;
    }

    /**
     * Get the isSystem value (if already known)
     *
     * @return bool - isSystem value
     */
    public function getIsSystem()
    {
        return $this->_isSystem;
    }

    /**
     * Set the isVolatile value
     *
     * @param bool $value - isVolatile value
     *
     * @return void
     */
    public function setIsVolatile($value)
    {
        assert(null === $value || is_bool($value));
        $this->_isVolatile = $value;
    }

    /**
     * Get the isVolatile value (if already known)
     *
     * @return bool - isVolatile value
     */
    public function getIsVolatile()
    {
        return $this->_isVolatile;
    }

    /**
     * Set the numberOfShards value
     *
     * @param int $value - numberOfShards value
     *
     * @return void
     */
    public function setNumberOfShards($value)
    {
        assert(null === $value || is_numeric($value));
        $this->_numberOfShards = $value;
    }

    /**
     * Get the numberOfShards value (if already known)
     *
     * @return mixed - numberOfShards value
     */
    public function getNumberOfShards()
    {
        return $this->_numberOfShards;
    }
    
    
    /**
     * Set the replicationFactor value
     *
     * @param int $value - replicationFactor value
     *
     * @return void
     */
    public function setReplicationFactor($value)
    {
        assert(null === $value || is_numeric($value) || $value === 'satellite');
        $this->_replicationFactor = $value;
    }

    /**
     * Get the replicationFactor value (if already known)
     *
     * @return mixed - replicationFactor value
     */
    public function getReplicationFactor()
    {
        return $this->_replicationFactor;
    }
    
    /**
     * Set the shardingStragy value
     *
     * @param string $value - shardingStrategy value
     *
     * @return void
     */
    public function setShardingStrategy($value)
    {
        $this->_shardingStrategy = $value;
    }

    /**
     * Get the sharding strategy value (if already known)
     *
     * @return mixed - shardingStrategy value
     */
    public function getShardingStrategy()
    {
        return $this->_shardingStrategy;
    }

    /**
     * Set the shardKeys value
     *
     * @param array $value - shardKeys value
     *
     * @return void
     */
    public function setShardKeys($value)
    {
        assert(null === $value || is_array($value));
        $this->_shardKeys = $value;
    }

    /**
     * Get the shardKeys value (if already known)
     *
     * @return array - shardKeys value
     */
    public function getShardKeys()
    {
        return $this->_shardKeys;
    }
}

class_alias(Collection::class, '\triagens\ArangoDb\Collection');
