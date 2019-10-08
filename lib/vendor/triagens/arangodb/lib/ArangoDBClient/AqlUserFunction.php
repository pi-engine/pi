<?php

/**
 * ArangoDB PHP client: AqlUserFunction
 *
 * @author    Frank Mayer
 * @copyright Copyright 2013, triagens GmbH, Cologne, Germany
 */

namespace ArangoDBClient;

/**
 * Provides management of user-functions
 *
 * AqlUserFunction object<br>
 * An AqlUserFunction is an object that is used to manage AQL User Functions.<br>
 * It registers, un-registers and lists user functions on the server<br>
 * <br>
 * The object encapsulates:<br>
 * <br>
 * <ul>
 * <li> the name of the function
 * <li> the actual javascript function
 * </ul>
 * <br>
 * The object requires the connection object and can be initialized
 * with or without initial configuration.<br>
 * <br>
 * Any configuration can be set and retrieved by the object's methods like this:<br>
 * <br>
 * <pre>
 * $this->setName('myFunctions:myFunction');<br>
 * $this->setCode('function (){your code};');
 * </pre>
 *
 * <br>
 * or like this:<br>
 * <br>
 * <pre>
 * $this->name('myFunctions:myFunction');<br>
 * $this->code('function (){your code};');
 * </pre>
 *
 * @property string $name - The name of the user function
 * @property string $code - The code of the user function
 * @property string _action
 *
 * @package   ArangoDBClient
 * @since     1.3
 */
class AqlUserFunction
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
     * The transaction's action.
     *
     * @var string
     */
    protected $_action = '';

    /**
     * Collections index
     */
    const ENTRY_NAME = 'name';

    /**
     * Action index
     */
    const ENTRY_CODE = 'code';


    /**
     * Initialise the AqlUserFunction object
     *
     * The $attributesArray array can be used to specify the name and code for the user function in form of an array.
     *
     * Example:
     * array(
     *   'name' => 'myFunctions:myFunction',
     *   'code' => 'function (){}'
     * )
     *
     *
     * @param Connection $connection      - the connection to be used
     * @param array      $attributesArray - user function initialization data
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function __construct(Connection $connection, array $attributesArray = null)
    {
        $this->_connection = $connection;
        if (is_array($attributesArray)) {
            $this->buildAttributesFromArray($attributesArray);
        }
    }


    /**
     * Registers the user function
     *
     * If no parameters ($name,$code) are passed, it will use the properties of the object.
     *
     * If $name and/or $code are passed, it will override the object's properties with the passed ones
     *
     * @param null $name
     * @param null $code
     *
     * @throws Exception throws exception if registration failed
     *
     * @return mixed true if registration was successful.
     */
    public function register($name = null, $code = null)
    {
        $attributes = $this->attributes;


        if ($name) {
            $attributes['name'] = $name;
        }

        if ($code) {
            $attributes['code'] = $code;
        }

        $response = $this->_connection->post(
            Urls::URL_AQL_USER_FUNCTION,
            $this->getConnection()->json_encode_wrapper($attributes)
        );

        return $response->getJson();
    }


    /**
     * Un-register the user function
     *
     * If no parameter ($name) is passed, it will use the property of the object.
     *
     * If $name is passed, it will override the object's property with the passed one
     *
     * @param string  $name
     * @param boolean $namespace
     *
     * @throws Exception throw exception if the request fails
     *
     * @return mixed true if successful without a return value or the return value if one was set in the action
     */
    public function unregister($name = null, $namespace = false)
    {
        if (null === $name) {
            $name = $this->getName();
        }

        $url = UrlHelper::buildUrl(Urls::URL_AQL_USER_FUNCTION, [$name]);

        if ($namespace) {
            $url = UrlHelper::appendParamsUrl($url, ['group' => true]);
        }

        $response = $this->_connection->delete($url);

        return $response->getJson();
    }


    /**
     * Get registered user functions
     *
     * The method can optionally be passed a $namespace parameter to narrow the results down to a specific namespace.
     *
     * @param null $namespace
     *
     * @throws Exception throw exception if the request failed
     *
     * @return mixed true if successful without a return value or the return value if one was set in the action
     */
    public function getRegisteredUserFunctions($namespace = null)
    {
        $url = UrlHelper::buildUrl(Urls::URL_AQL_USER_FUNCTION, []);
        if (null !== $namespace) {
            $url = UrlHelper::appendParamsUrl($url, ['namespace' => $namespace]);
        }
        $response = $this->_connection->get($url);

        $data = $response->getJson();
        if (isset($data['result'])) {
            return $data['result'];
        }
        // backwards compatibility
        return $data;
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
    public function setName($value)
    {
        $this->set(self::ENTRY_NAME, (string) $value);
    }


    /**
     * Get name value
     *
     * @return string name
     */
    public function getName()
    {
        return $this->get(self::ENTRY_NAME);
    }

    /**
     * Set user function code
     *
     * @param string $value
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function setCode($value)
    {
        $this->set(self::ENTRY_CODE, (string) $value);
    }


    /**
     * Get user function code
     *
     * @return string name
     */
    public function getCode()
    {
        return $this->get(self::ENTRY_CODE);
    }


    /**
     * Set an attribute
     *
     * @param $key
     * @param $value
     *
     * @return $this
     * @throws ClientException
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new ClientException('Invalid attribute key');
        }

        $this->attributes[$key] = $value;

        return $this;
    }


    /**
     * Set an attribute, magic method
     *
     * This is a magic method that allows the object to be used without
     * declaring all attributes first.
     *
     * @throws ClientException
     *
     * @magic
     *
     * @param string $key   - attribute name
     * @param mixed  $value - value for attribute
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case self::ENTRY_NAME :
                $this->setName($value);
                break;
            case self::ENTRY_CODE :
                $this->setCode($value);
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
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return null;
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
    public function buildAttributesFromArray($options)
    {
        if (isset($options[self::ENTRY_NAME])) {
            $this->setName($options[self::ENTRY_NAME]);
        }

        if (isset($options[self::ENTRY_CODE])) {
            $this->setCode($options[self::ENTRY_CODE]);
        }
    }
}

class_alias(AqlUserFunction::class, '\triagens\ArangoDb\AqlUserFunction');
