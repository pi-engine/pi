<?php

/**
 * ArangoDB PHP client: single document
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @author    Frank Mayer
 * @copyright Copyright 2012, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Value object representing a single collection-based document
 *
 * <br>
 *
 * @package   ArangoDBClient
 * @since     0.2
 */
class Document implements \JsonSerializable
{
    /**
     * The document id (might be NULL for new documents)
     *
     * @var string - document id
     */
    protected $_id;

    /**
     * The document key (might be NULL for new documents)
     *
     * @var string - document key
     */
    protected $_key;

    /**
     * The document revision (might be NULL for new documents)
     *
     * @var mixed
     */
    protected $_rev;

    /**
     * The document attributes (names/values)
     *
     * @var array
     */
    protected $_values = [];

    /**
     * Flag to indicate whether document was changed locally
     *
     * @var bool
     */
    protected $_changed = false;

    /**
     * Flag to indicate whether document is a new document (never been saved to the server)
     *
     * @var bool
     */
    protected $_isNew = true;

    /**
     * Flag to indicate whether validation of document values should be performed
     * This can be turned on, but has a performance penalty
     *
     * @var bool
     */
    protected $_doValidate = false;

    /**
     * An array, that defines which attributes should be treated as hidden.
     *
     * @var array
     */
    protected $_hiddenAttributes = [];

    /**
     * Flag to indicate whether hidden attributes should be ignored or included in returned data-sets
     *
     * @var bool
     */
    protected $_ignoreHiddenAttributes = false;

    /**
     * Document id index
     */
    const ENTRY_ID = '_id';

    /**
     * Document key index
     */
    const ENTRY_KEY = '_key';

    /**
     * Revision id index
     */
    const ENTRY_REV = '_rev';

    /**
     * isNew id index
     */
    const ENTRY_ISNEW = '_isNew';

    /**
     * hidden attribute index
     */
    const ENTRY_HIDDENATTRIBUTES = '_hiddenAttributes';

    /**
     * hidden attribute index
     */
    const ENTRY_IGNOREHIDDENATTRIBUTES = '_ignoreHiddenAttributes';

    /**
     * waitForSync option index
     */
    const OPTION_WAIT_FOR_SYNC = 'waitForSync';

    /**
     * policy option index
     */
    const OPTION_POLICY = 'policy';

    /**
     * keepNull option index
     */
    const OPTION_KEEPNULL = 'keepNull';

    /**
     * Constructs an empty document
     *
     * @param array $options           - optional, initial $options for document
     *                                 <p>Options are :<br>
     *                                 <li>'_hiddenAttributes' - Set an array of hidden attributes for created documents.
     *                                 <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                                 <p>
     *
     */
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            // keeping the non-underscored version for backwards-compatibility
            if (isset($options['hiddenAttributes'])) {
                $this->setHiddenAttributes($options['hiddenAttributes']);
            }
            if (isset($options[self::ENTRY_HIDDENATTRIBUTES])) {
                $this->setHiddenAttributes($options[self::ENTRY_HIDDENATTRIBUTES]);
            }
            if (isset($options[self::ENTRY_IGNOREHIDDENATTRIBUTES])) {
                $this->setIgnoreHiddenAttributes($options[self::ENTRY_IGNOREHIDDENATTRIBUTES]);
            }
            if (isset($options[self::ENTRY_ISNEW])) {
                $this->setIsNew($options[self::ENTRY_ISNEW]);
            }
            if (isset($options['_validate'])) {
                $this->_doValidate = $options['_validate'];
            }
        }
    }

    /**
     * Factory method to construct a new document using the values passed to populate it
     *
     * @throws ClientException
     *
     * @param array $values  - initial values for document
     * @param array $options - optional, initial options for document
     *
     * @return Document|Edge|Graph
     */
    public static function createFromArray($values, array $options = [])
    {
        $document = new static($options);
        foreach ($values as $key => $value) {
            $document->set($key, $value);
        }

        $document->setChanged(true);

        return $document;
    }

    /**
     * Clone a document
     *
     * Returns the clone
     *
     * @magic
     *
     * @return void
     */
    public function __clone()
    {
        $this->_id  = null;
        $this->_key = null;
        $this->_rev = null;
        // do not change the _changed flag here
    }

    /**
     * Get a string representation of the document.
     *
     * It will not output hidden attributes.
     *
     * Returns the document as JSON-encoded string
     *
     * @magic
     *
     * @return string - JSON-encoded document
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Returns the document as JSON-encoded string
     *
     * @param array $options - optional, array of options that will be passed to the getAll function
     *                       <p>Options are :
     *                       <li>'_includeInternals' - true to include the internal attributes. Defaults to false</li>
     *                       <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                       </p>
     *
     * @return string - JSON-encoded document
     */
    public function toJson(array $options = [])
    {
        return json_encode($this->getAll($options));
    }

    /**
     * Returns the document as a serialized string
     *
     * @param array $options - optional, array of options that will be passed to the getAll function
     *                       <p>Options are :
     *                       <li>'_includeInternals' - true to include the internal attributes. Defaults to false</li>
     *                       <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                       </p>
     *
     * @return string - PHP serialized document
     */
    public function toSerialized(array $options = [])
    {
        return serialize($this->getAll($options));
    }

    /**
     * Returns the attributes with the hidden ones removed
     *
     * @param array $attributes - attributes array
     *
     * @param array $_hiddenAttributes
     *
     * @return array - attributes array
     */
    public function filterHiddenAttributes($attributes, array $_hiddenAttributes = [])
    {
        $hiddenAttributes = $_hiddenAttributes !== null ? $_hiddenAttributes : $this->getHiddenAttributes();

        if (count($hiddenAttributes) > 0) {
            foreach ($hiddenAttributes as $hiddenAttributeName) {
                if (isset($attributes[$hiddenAttributeName])) {
                    unset($attributes[$hiddenAttributeName]);
                }
            }
        }

        unset ($attributes[self::ENTRY_HIDDENATTRIBUTES]);

        return $attributes;
    }

    /**
     * Set a document attribute
     *
     * The key (attribute name) must be a string.
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
        if ($this->_doValidate) {
            // validate the value passed
            ValueValidator::validate($value);
        }

        if ($key[0] === '_') {
            if ($key === self::ENTRY_ID) {
                $this->setInternalId($value);

                return;
            }

            if ($key === self::ENTRY_KEY) {
                $this->setInternalKey($value);

                return;
            }

            if ($key === self::ENTRY_REV) {
                $this->setRevision($value);

                return;
            }

            if ($key === self::ENTRY_ISNEW) {
                $this->setIsNew($value);

                return;
            }
        }

        if (!$this->_changed) {
            if (!isset($this->_values[$key]) || $this->_values[$key] !== $value) {
                // set changed flag
                $this->_changed = true;
            }
        }

        // and store the value
        $this->_values[$key] = $value;
    }

    /**
     * Set a document attribute, magic method
     *
     * This is a magic method that allows the object to be used without
     * declaring all document attributes first.
     * This function is mapped to set() internally.
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
        $this->set($key, $value);
    }

    /**
     * Get a document attribute
     *
     * @param string $key - name of attribute
     *
     * @return mixed - value of attribute, NULL if attribute is not set
     */
    public function get($key)
    {
        if (isset($this->_values[$key])) {
            return $this->_values[$key];
        }

        return null;
    }

    /**
     * Get a document attribute, magic method
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
        if (isset($this->_values[$key])) {
            return true;
        }

        return false;
    }

    /**
     * Magic method to unset an attribute.
     * Caution!!! This works only on the first array level.
     * The preferred method to unset attributes in the database, is to set those to null and do an update() with the option: 'keepNull' => false.
     *
     * @magic
     *
     * @param $key
     */
    public function __unset($key)
    {
        unset($this->_values[$key]);
    }

    /**
     * Get all document attributes
     *
     * @param mixed $options - optional, array of options for the getAll function, or the boolean value for $includeInternals
     *                       <p>Options are :
     *                       <li>'_includeInternals' - true to include the internal attributes. Defaults to false</li>
     *                       <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                       </p>
     *
     * @return array - array of all document attributes/values
     */
    public function getAll(array $options = [])
    {
        // This preserves compatibility for the old includeInternals parameter.
        $includeInternals       = false;
        $ignoreHiddenAttributes = $this->{self::ENTRY_IGNOREHIDDENATTRIBUTES};
        $_hiddenAttributes      = $this->{self::ENTRY_HIDDENATTRIBUTES};

        if (!is_array($options)) {
            $includeInternals = $options;
        } else {
            // keeping the non-underscored version for backwards-compatibility
            $includeInternals = array_key_exists(
                'includeInternals',
                $options
            ) ? $options['includeInternals'] : $includeInternals;

            $includeInternals = array_key_exists(
                '_includeInternals',
                $options
            ) ? $options['_includeInternals'] : $includeInternals;

            // keeping the non-underscored version for backwards-compatibility
            $ignoreHiddenAttributes = array_key_exists(
                'ignoreHiddenAttributes',
                $options
            ) ? $options['ignoreHiddenAttributes'] : $ignoreHiddenAttributes;

            $ignoreHiddenAttributes = array_key_exists(
                self::ENTRY_IGNOREHIDDENATTRIBUTES,
                $options
            ) ? $options[self::ENTRY_IGNOREHIDDENATTRIBUTES] : $ignoreHiddenAttributes;

            $_hiddenAttributes = array_key_exists(
                self::ENTRY_HIDDENATTRIBUTES,
                $options
            ) ? $options[self::ENTRY_HIDDENATTRIBUTES] : $_hiddenAttributes;
        }

        $data         = $this->_values;
        $nonInternals = ['_changed', '_values', self::ENTRY_HIDDENATTRIBUTES];

        if ($includeInternals === true) {
            foreach ($this as $key => $value) {
                if ($key[0] === '_' && 0 !== strpos($key, '__') && !in_array($key, $nonInternals, true)) {
                    $data[$key] = $value;
                }
            }
        }

        if ($ignoreHiddenAttributes === false) {
            $data = $this->filterHiddenAttributes($data, $_hiddenAttributes);
        }

        if (null !== $this->_key) {
            $data['_key'] = $this->_key;
        }

        return $data;
    }

    /**
     * Get all document attributes for insertion/update
     *
     * @return mixed - associative array of all document attributes/values
     */
    public function getAllForInsertUpdate()
    {
        $data = [];
        foreach ($this->_values as $key => $value) {
            if ($key === '_id' || $key === '_rev') {
                continue;
            }

            if ($key === '_key' && $value === null) {
                // key value not yet set
                continue;
            }
            $data[$key] = $value;
        }
        if ($this->_key !== null) {
            $data['_key'] = $this->_key;
        }

        return $data;
    }


    /**
     * Get all document attributes, and return an empty object if the documentapped into a DocumentWrapper class
     *
     * @param mixed $options - optional, array of options for the getAll function, or the boolean value for $includeInternals
     *                       <p>Options are :
     *                       <li>'_includeInternals' - true to include the internal attributes. Defaults to false</li>
     *                       <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                       </p>
     *
     * @return mixed - associative array of all document attributes/values, or an empty StdClass if the document
     *                 does not have any
     */
    public function getAllAsObject(array $options = [])
    {
        $result = $this->getAll($options);
        if (count($result) === 0) {
            return new \stdClass();
        }

        return $result;
    }

    /**
     * Set the hidden attributes
     * $cursor
     *
     * @param array $attributes - array of attributes
     *
     * @return void
     */
    public function setHiddenAttributes(array $attributes)
    {
        $this->{self::ENTRY_HIDDENATTRIBUTES} = $attributes;
    }

    /**
     * Get the hidden attributes
     *
     * @return array $attributes - array of attributes
     */
    public function getHiddenAttributes()
    {
        return $this->{self::ENTRY_HIDDENATTRIBUTES};
    }

    /**
     * @return boolean
     */
    public function isIgnoreHiddenAttributes()
    {
        return $this->{self::ENTRY_IGNOREHIDDENATTRIBUTES};
    }

    /**
     * @param boolean $ignoreHiddenAttributes
     */
    public function setIgnoreHiddenAttributes($ignoreHiddenAttributes)
    {
        $this->{self::ENTRY_IGNOREHIDDENATTRIBUTES} = (bool) $ignoreHiddenAttributes;
    }

    /**
     * Set the changed flag
     *
     * @param bool $value - change flag
     *
     * @return bool
     */
    public function setChanged($value)
    {
        return $this->_changed = (bool) $value;
    }

    /**
     * Get the changed flag
     *
     * @return bool - true if document was changed, false otherwise
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * Set the isNew flag
     *
     * @param bool $isNew - flags if new or existing doc
     *
     * @return void
     */
    public function setIsNew($isNew)
    {
        $this->_isNew = (bool) $isNew;
    }

    /**
     * Get the isNew flag
     *
     * @return bool $isNew - flags if new or existing doc
     */
    public function getIsNew()
    {
        return $this->_isNew;
    }

    /**
     * Set the internal document id
     *
     * This will throw if the id of an existing document gets updated to some other id
     *
     * @throws ClientException
     *
     * @param string $id - internal document id
     *
     * @return void
     */
    public function setInternalId($id)
    {
        if ($this->_id !== null && $this->_id !== $id) {
            throw new ClientException('Should not update the id of an existing document');
        }


        if (!preg_match('/^[a-zA-Z0-9_-]{1,64}\/[a-zA-Z0-9_:.@\-()+,=;$!*\'%]{1,254}$/', $id)) {
            throw new ClientException('Invalid format for document id');
        }

        $this->_id = (string) $id;
    }

    /**
     * Set the internal document key
     *
     * This will throw if the key of an existing document gets updated to some other key
     *
     * @throws ClientException
     *
     * @param string $key - internal document key
     *
     * @return void
     */
    public function setInternalKey($key)
    {
        if ($this->_key !== null && $this->_key !== $key) {
            throw new ClientException('Should not update the key of an existing document');
        }

        if (!preg_match('/^[a-zA-Z0-9_:.@\-()+,=;$!*\'%]{1,254}$/', $key)) {
            throw new ClientException('Invalid format for document key');
        }

        $this->_key = (string) $key;
    }

    /**
     * Get the internal document id (if already known)
     *
     * Document ids are generated on the server only. Document ids consist of collection id and
     * document id, in the format collectionId/documentId
     *
     * @return string - internal document id, might be NULL if document does not yet have an id
     */
    public function getInternalId()
    {
        return $this->_id;
    }

    /**
     * Get the internal document key (if already known)
     *
     * @return string - internal document key, might be NULL if document does not yet have a key
     */
    public function getInternalKey()
    {
        return $this->_key;
    }

    /**
     * Convenience function to get the document handle (if already known) - is an alias to getInternalId()
     *
     * Document handles are generated on the server only. Document handles consist of collection id and
     * document id, in the format collectionId/documentId
     *
     * @return string - internal document id, might be NULL if document does not yet have an id
     */
    public function getHandle()
    {
        return $this->getInternalId();
    }

    /**
     * Get the document id (or document handle) if already known.
     *
     * It is a string and consists of the collection's name and the document key (_key attribute) separated by /.
     * Example: (collectionname/documentId)
     *
     * The document handle is stored in a document's _id attribute.
     *
     * @return mixed - document id, might be NULL if document does not yet have an id.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get the document key (if already known).
     * Alias function for getInternalKey()
     *
     * @return mixed - document key, might be NULL if document does not yet have a key
     */
    public function getKey()
    {
        return $this->getInternalKey();
    }

    /**
     * Get the collection id (if already known)
     *
     * Collection ids are generated on the server only. Collection ids are numeric but might be
     * bigger than PHP_INT_MAX. To reliably store a collection id elsewhere, a PHP string should be used
     *
     * @return mixed - collection id, might be NULL if document does not yet have an id
     */
    public function getCollectionId()
    {
        @list($collectionId) = explode('/', $this->_id, 2);

        return $collectionId;
    }

    /**
     * Set the document revision
     *
     * Revision ids are generated on the server only.
     *
     * Document ids are strings, even if they look "numeric"
     * To reliably store a document id elsewhere, a PHP string must be used
     *
     * @param mixed $rev - revision id
     *
     * @return void
     */
    public function setRevision($rev)
    {
        $this->_rev = (string) $rev;
    }

    /**
     * Get the document revision (if already known)
     *
     * @return mixed - revision id, might be NULL if document does not yet have an id
     */
    public function getRevision()
    {
        return $this->_rev;
    }

    /**
     * Get all document attributes
     * Alias function for getAll() - it's necessary for implementing JsonSerializable interface
     *
     * @param mixed $options - optional, array of options for the getAll function, or the boolean value for $includeInternals
     *                       <p>Options are :
     *                       <li>'_includeInternals' - true to include the internal attributes. Defaults to false</li>
     *                       <li>'_ignoreHiddenAttributes' - true to show hidden attributes. Defaults to false</li>
     *                       </p>
     *
     * @return array - array of all document attributes/values
     */
    public function jsonSerialize(array $options = [])
    {
        return $this->getAll($options);
    }
}

class_alias(Document::class, '\triagens\ArangoDb\Document');
