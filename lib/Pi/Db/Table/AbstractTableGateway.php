<?PHP
/**
 * Pi Table Gateway
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Db
 * @subpackage      Table
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Db\Table;

use ArrayObject;
use Pi;
use Pi\Application\Db;
use Zend\Db\RowGateway\AbstractRowGateway;
use Zend\Db\TableGateway\AbstractTableGateway as ZendAbstractTableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
//use Zend\Db\ResultSet\Row;
use Zend\Db\Metadata\Metadata;
use Pi\Db\Sql\Where;

abstract class AbstractTableGateway extends ZendAbstractTableGateway
{
    /**
     * Class for select result prototype
     * @var string
     */
    protected $resultSetClass;

    /**
     * Class for row or row gateway
     * @var string
     */
    protected $rowClass;

    /**
     * Columns to be endcoded/decoded during save/fetch with DB, only applicable to RowGateway
     * @var array
     */
    protected $encodeColumns = array(
        // column name => convert to associative array?
        //'col_array'     => true,
        //'col_object'    => false,
    );

    /**
     * Primary key
     * @var string
     */
    protected $primaryKeyColumn;

    /**
     *
     * @var Metadata
     */
    protected $metadata;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setup($options);
        $this->initialize();
    }

    /**
     * Setup model
     * @param array $options
     * @return AbstractTableGateway
     */
    public function setup($options = array())
    {
        $tablePrefix = '';
        if (isset($options['prefix'])) {
            $tablePrefix = $options['prefix'];
            unset($options['prefix']);
        }
        $tableName = '';
        if (isset($options['name'])) {
            $tableName = $options['name'];
            unset($options['name']);
        }

        // process features
        if (isset($options['features'])) {
            if ($options['features'] instanceof Feature\AbstractFeature) {
                $options['features'] = array($options['features']);
            }
            if (is_array($options['features'])) {
                $this->featureSet = new Feature\FeatureSet($options['features']);
            } elseif ($options['features'] instanceof Feature\FeatureSet) {
                $this->featureSet = $options['features'];
            } else {
                throw new \InvalidArgumentException(
                    'TableGateway expects $options["feature"] to be an instance of an AbstractFeature or a FeatureSet, or an array of AbstractFeatures'
                );
            }
            unset($options['features']);
        }

        // Properties: table, schema, adapter, masterAdapter, slaveAdapter, sql, selectResultPrototype, resultSetClass, rowClass, primaryKeyColumn
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
        // Setup table
        if (!$this->table && $tableName) {
            $this->table = $tableName;
        }
        if ($tablePrefix) {
            $this->table = $tablePrefix . $this->table;
        }
        return $this;
    }

    public function initialize()
    {
        if ($this->isInitialized) {
            return;
        }

        $this->sql = $this->sql ?: new Sql($this->adapter, $this->table);

        if (!$this->resultSetPrototype) {
            $rowObjectPrototype = $this->createRow();
            if ($this->resultSetClass) {
                $resultSetPrototype = new $this->resultSetClass(null, $rowObjectPrototype);
            } else {
                $resultSetPrototype = new ResultSet(null, $rowObjectPrototype);
            }
            $this->resultSetPrototype = $resultSetPrototype;
        }

        parent::initialize();
    }

    /**
     * Set adapter
     *
     * @param Adapter $adapter
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Select
     *
     * @param  string $where
     * @return type
     */
    public function select($where = null)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        if (null === $where) {
           return $this->sql->select();
        }
        return parent::select($where);
    }

    /**#@APIs+*/
    /**
     * Creates Row object
     *
     * @param  array $data
     * @return RowGateway|Row
     */
    public function createRow($data = null)
    {
        if (!$this->rowClass) {
            $row = new ArrayObject;
        } elseif (is_subclass_of($this->rowClass, 'Zend\\Db\\RowGateway\\AbstractRowGateway')) {
            $row = new $this->rowClass($this->primaryKeyColumn, $this->table, $this->sql);
            if ($this->encodeColumns) {
                $row->setEncodeColumns($this->encodeColumns);
            }
        } else {
            $row = new $this->rowClass;
        }
        if (null !== $data) {
            $row->populate($data, false);
        }
        return $row;
    }

    /**
     * Set columns to be encode/decode
     *
     * @param array $columns
     * @return AbstractTableGateway
     */
    public function setEncodeColumns(array $columns)
    {
        $this->encodeColumns = $columns;
        return $this;
    }

    /**
     * Quote identifier
     *
     * @param  string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $this->adapter->getPlatform()->quoteIdentifier($identifier);
    }

    /**
     * Quote value
     *
     * @param  string $value
     * @return string
     */
    public function quoteValue($value)
    {
        return $this->adapter->getPlatform()->quoteValue($value);
    }

    /**
     * @param string $name
     * @param string|null $type
     * @return string
     */
    public function formatParameterName($name, $type = null)
    {
        return $this->adapter->getDriver()->formatParameterName($name, $type);
    }

    /**
     * Fetches row(s) by primary key or specified column. The argument specifies one or more
     * key value(s).  To find multiple rows, the argument must be an array.
     *
     * The find() method returns a ResultSet object if key array is provided or a Row object if a single key value is provided.
     *
     * @param  array|string|int $key The value(s) of the key.
     * @return ResultSet|Row Row(s) matching the criteria.
     */
    public function find($key, $column = null)
    {
        $column = $column ?: $this->primaryKeyColumn;
        if (!$column) {
            throw new \Exception('No column is specified.');
        }
        $isScalar = false;
        if (!is_array($key)) {
            $isScalar = true;
            $key = array($key);
        }
        $where = new Where;
        if (count($key) == 1) {
            $where->equalTo($column, $key[0]);
        } else {
            $where->in($column, $key);
        }
        $select = $this->select()->where($where); //->limit(1);
        $resultSet = $this->selectWith($select);

        $result = $isScalar ? $resultSet->current() : $resultSet;
        return $result;
    }

    /**
     * Get Metadata
     */
    public function metadata()
    {
        if (!$this->metadata) {
            $this->metadata = new Metadata($this->adapter);
        }

        return $this->metadata;
    }

    /**
     * Add a feature to FeatureSet
     *
     * @param string $name
     * @return AbstractTableGateway
     */
    public function addFeature($name)
    {
        $featureClass = sprintf('%s\\Feature\\%sFeature', __NAMESPECE, $name);
        if (!class_exists($featureClass)) {
            $featureClass = sprintf('Zend\Db\\TableGateway\\Feature\\%sFeature', $name);
        }
        $this->featureSet->addFeature(new $featureClass);
        return $this;
    }
}
