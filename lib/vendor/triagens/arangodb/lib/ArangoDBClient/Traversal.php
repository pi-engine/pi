<?php

/**
 * ArangoDB PHP client: Traversal
 *
 * @author    Frank Mayer
 * @copyright Copyright 2013, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Provides graph traversal
 *
 * A Traversal object is used to execute a graph traversal on the server side.<br>
 * <br>
 *
 * The object requires the connection object, the startVertex, the edgeCollection and the optional parameters.<br>
 * <br>
 *
 * @link      https://docs.arangodb.com/HTTP/Traversal/index.html
 *
 * @package   ArangoDBClient
 * @since     1.4
 */
class Traversal
{
    /**
     * The connection object
     *
     * @var Connection
     */
    private $_connection;

    /**
     * The traversal's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * count fields
     */
    const OPTION_FIELDS = 'fields';

    /**
     * Collections index
     */
    const ENTRY_STARTVERTEX = 'startVertex';

    /**
     * Action index
     */
    const ENTRY_EDGECOLLECTION = 'edgeCollection';

    /**
     * @var $_action string The action property of the traversal.
     */
    protected $_action;

    /**
     * Initialise the Traversal object
     *
     * @param Connection $connection     - the connection to be used
     * @param string     $startVertex    - user function initialization data
     * @param string     $edgeCollection - user function initialization data
     * @param array      $options
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function __construct(Connection $connection, $startVertex, $edgeCollection, array $options = null)
    {
        $this->_connection = $connection;
        $this->setStartVertex($startVertex);
        $this->setEdgeCollection($edgeCollection);

        if (is_array($options)) {
            $this->attributes = array_merge($this->attributes, $options);
        }
    }


    /**
     * Execute and get the traversal result
     *
     * @return array $responseArray
     * @throws \ArangoDBClient\Exception
     * @throws \ArangoDBClient\ClientException
     */
    public function getResult()

    {
        $bodyParams = $this->attributes;


        $response = $this->_connection->post(
            Urls::URL_TRAVERSAL,
            $this->getConnection()->json_encode_wrapper($bodyParams)
        );

        return $response->getJson();
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
     * Set name of the user function. It must have at least one namespace, but also can have sub-namespaces.
     * correct:
     * 'myNamespace:myFunction'
     * 'myRootNamespace:mySubNamespace:myFunction'
     *
     * wrong:
     * 'myFunction'
     *
     *
     * @param string $value
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function setStartVertex($value)
    {
        $this->set(self::ENTRY_STARTVERTEX, (string) $value);
    }


    /**
     * Get name value
     *
     * @return string name
     */
    public function getStartVertex()
    {
        return $this->get(self::ENTRY_STARTVERTEX);
    }

    /**
     * Set user function code
     *
     * @param string $value
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function setEdgeCollection($value)
    {
        $this->set(self::ENTRY_EDGECOLLECTION, (string) $value);
    }


    /**
     * Get user function code
     *
     * @return string name
     */
    public function getEdgeCollection()
    {
        return $this->get(self::ENTRY_EDGECOLLECTION);
    }


    /**
     * Set an attribute
     *
     * @param $key
     * @param $value
     *
     * @throws ClientException
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new ClientException('Invalid attribute key');
        }

        $this->attributes[$key] = $value;
    }


    /**
     * Set an attribute, magic method
     *
     * This is a magic method that allows the object to be used without
     * declaring all attributes first.
     *
     * @throws ClientException
     *
     * @param string $key   - attribute name
     * @param mixed  $value - value for attribute
     *
     * @magic
     *
     * @return void
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case self::ENTRY_STARTVERTEX :
                $this->setStartVertex($value);
                break;
            case self::ENTRY_EDGECOLLECTION :
                $this->setEdgeCollection($value);
                break;
            default:
                $this->set($key, $value);
                break;
        }
    }

    /**
     * Get an attribute
     *
     * @magic
     *
     * @param string $key - name of attribute
     *
     * @return mixed - value of attribute, NULL if attribute is not set
     */
    public function get($key)
    {
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
}

class_alias(Traversal::class, '\triagens\ArangoDb\Traversal');
