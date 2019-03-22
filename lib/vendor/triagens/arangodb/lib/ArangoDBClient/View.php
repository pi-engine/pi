<?php

/**
 * ArangoDB PHP client: View class
 *
 * @package   ArangoDBClient
 * @author    Jan Steemann
 * @copyright Copyright 2018, ArangoDB GmbH, Cologne, Germany
 *
 * @since     3.4
 */

namespace ArangoDBClient;

/**
 * Value object representing a view
 *
 * <br>
 *
 * @package   ArangoDBClient
 * @since     3.4
 */
class View
{
    /**
     * The view id (might be NULL for new views)
     *
     * @var string - view id
     */
    protected $_id;

    /**
     * The view name
     *
     * @var string - view name
     */
    protected $_name;
    
    /**
     * View id index
     */
    const ENTRY_ID = 'id';

    /**
     * View name index
     */
    const ENTRY_NAME = 'name';
    
    /**
     * View type index
     */
    const ENTRY_TYPE = 'type';
    
    /**
     * Constructs an empty view
     *
     * @param array $name       - name for view
     * @param string $type      - view type
     *
     * @since     3.4
     *
     * @throws \ArangoDBClient\ClientException
     */
    public function __construct($name, $type)
    {
        $this->_name = $name;
        $this->_type = $type;
    }

    /**
     * Return the view id
     *
     * @return string - view id
     */
    public function getId() 
    {
        return $this->_id;
    }
    
    /**
     * Set the view's id
     *
     * @param string - view id
     *
     * @return void
     */
    public function setId($id) 
    {
        $this->_id = $id;
    }
    
    /**
     * Return the view name
     *
     * @return string - view name
     */
    public function getName() 
    {
        return $this->_name;
    }
    
    /**
     * Return the view type
     *
     * @return string - view type
     */
    public function getType() 
    {
        return $this->_type;
    }
    
    /**
     * Return the view as an array
     *
     * @return array - view data as an array
     */
    public function getAll() 
    {
        return [
            self::ENTRY_ID         => $this->getId(),
            self::ENTRY_NAME       => $this->getName(),
            self::ENTRY_TYPE       => $this->getType(),
        ];
    }
}

class_alias(View::class, '\triagens\ArangoDb\View');
