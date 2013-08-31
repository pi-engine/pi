<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

use PDO;
use Pi;
use Pi\Db\Sql\Where;
use Pi\Db\Adapter\Adapter;
use Pi\Db\Table\AbstractTableGateway;
use Pi\Log\DbProfiler;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Update;

/**
 * Pi DB service gateway
 *
 * Note:
 * In installation sql scripts, quote all database names with `{` and `}`
 * so that all names can be canonized with prefix on installation.
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Db
{
    /**
     * Custom statement class for PDO
     *
     * @var string
     * @see http://www.php.net/manual/en/pdo.setattribute.php
     */
    const STATEMENT_CLASS = 'Pi\Db\Adapter\Driver\Statement';

    /**
     * Connection mode: Master-Slave, Single
     */
    //protected $mode = '';

    /**
     * Database schema
     *
     * @var string
     */
    protected $schema;

    /**
     * Driver adapter
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Master-Slave adapters
     *
     * @var array
     */
    protected $adapterMasterSlave = array(
        'master'    => null,
        'slave'     => null
    );

    /**
     * Database metadata adapter
     *
     * @var Metadata
     */
    protected $metadata;

    /**
     * Loaded models
     *
     * @var array
     */
    protected $model = array();

    /**
     * Name prefix for all tables
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Name prefix for system tables
     *
     * @var string
     */
    protected $corePrefix = 'core_';

    /**
     * DB profiling logger
     *
     * @var DbProfiler
     */
    protected $profiler;

    /**
     * Constructor
     *
     * Build DB handler with options:
     *  - connection: Database connection parameters
     *    - Single DB mode:
     *      'driver', 'dsn', 'username', 'password', 'options'
     *      [, 'connect_onload']
     *    - Master-Slave mode: 'master', 'slave'
     *  - table_prefix: database table prefix;
     *  - core_prefix: core table prefix
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $this->loadAdapter($options['connection']);
        if (isset($options['table_prefix'])) {
            $this->setTablePrefix($options['table_prefix']);
        }
        if (isset($options['core_prefix'])) {
            $this->setCorePrefix($options['core_prefix']);
        }
    }

    /**
     * Loads adatpers
     *
     * @param array $options
     * @return self
     */
    public function loadAdapter($options)
    {
        if (isset($options['master'])) {
            $adapterMaster = $this->createAdapter($options['master']);
            $adapterSlave = $this->createAdapter($options['slave']);
            $this->setAdapter($adapterMaster, 'master')
                 ->setAdapter($adapterSlave, 'slave');
        } else {
            $adapter = $this->createAdapter($options);
            $this->setAdapter($adapter);
        }

        return $this;
    }

    /**
     * Set table prefix
     *
     * @param string $prefix
     * @return self
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        return $this;
    }

    /**
     * Get table prefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * Set system table prefix
     *
     * @param string $prefix
     * @return self
     */
    public function setCorePrefix($prefix)
    {
        $this->corePrefix = $prefix;
        return $this;
    }

    /**
     * Get system table prefix
     *
     * @return string
     */
    public function getCorePrefix()
    {
        return $this->corePrefix;
    }

    /**
     * Set database schema
     *
     * @param string $schema
     * @return self
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Get database schema
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get database schema
     *
     * @return void
     * @see getSchema()
     */
    public function schema()
    {
        return $this->getSchema();
    }

    /**
     * Create adapter with configs
     *
     * Configs are canonized to avoid violiation of `driver_options`
     * in {@link \Zend\Db\Adapter\Driver\Pdo\Connection::connect()}
     *
     * @param array                                 $config
     * @param \Zend\Db\Adapter\AdapterInterface   $platform
     * @return Adapter
     */
    public function createAdapter(array $config, $platform = null)
    {
        // Canonize config
        $options = array();
        if (isset($config['options'])) {
            $options = $config['options'];
            unset($config['options']);
        }

        // Set user-supplied statement class derived from PDOStatement.
        // Cannot be used with persistent PDO instances.
        // @see http://www.php.net/manual/en/pdo.setattribute.php
        if (!isset($config['driver_options'][PDO::ATTR_STATEMENT_CLASS])) {
            $config['driver_options'][PDO::ATTR_STATEMENT_CLASS] = array(
                static::STATEMENT_CLASS,
                array($this->profiler() ?: null)
            );
        }

        $adapter = new Adapter($config, $platform);

        return $adapter;
    }

    /**
     * Set adapter
     *
     * @param Adapter       $adapter
     * @param null|string   $type     `master` or `slave`, default as null
     * @return self
     */
    public function setAdapter(Adapter $adapter, $type = null)
    {
        if ($type) {
            $this->adapterMasterSlave[$type] = $adapter;
        } else {
            $this->adapter = $adapter;
        }

        return $this;
    }

    /**
     * Get adatper
     *
     * @param null|string $type `master` or `slave`, default as null
     * @return Adapter
     */
    public function getAdapter($type = null)
    {
        if ($type) {
            return $this->adapterMasterSlave[$type];
        } else {
            return $this->adapter;
        }
    }

    /**
     * Get adatper
     *
     * @param null|string $type `master` or `slave`, default as null
     * @return Adapter
     */
    public function adapter($type = null)
    {
        return $this->getAdapter($type);
    }

    /**
     * Static method to add prefx to a table and get its full name
     *
     * @param string $table
     * @param string $type
     * @return string
     */
    public function prefix($table = '', $type = '')
    {
        $typePrefix = empty($type) || $type == 'core'
                      ? $this->corePrefix : $type . '_';
        return sprintf('%s%s%s', $this->tablePrefix, $typePrefix, $table);
    }

    /**
     * Loads a model
     *
     * Sample:
     *
     *  - Load a normal model:
     *      `Pi::db()->model(<model-name>)`
     *  - Load a model with no defined model class:
     *      `Pi::db()->model(<model-name>)`
     *  - Load a nest model with no defined model class:
     *      `Pi::db()->model(<model-name>, array('type' => 'nest'))`
     *
     * @param string    $name
     * @param array     $options
     * @return AbstractTableGateway
     */
    public function model($name, $options = array())
    {
        $name = strtolower($name);

        if (!isset($this->model[$name])) {
            $pos = strpos($name, '/');
            if ($pos) {
                list($module, $key) = explode('/', $name, 2);
            } else {
                $module = '';
                $key = $name;
            }
            $className = str_replace(
                ' ',
                '\\',
                ucwords(str_replace('_', ' ', $key))
            );
            if ($module) {
                $className = sprintf(
                    'Module\\%s\Model\\%s',
                    ucfirst($module),
                    $className
                );
                $options['prefix'] = static::prefix('', $module);
            } else {
                $className = sprintf('Pi\Application\Model\\%s', $className);
                $options['prefix'] = static::prefix('', 'core');
            }
            if (!class_exists($className)) {
                if  (isset($options['type'])) {
                    $type = ucfirst($options['type']);
                    unset($options['type']);
                } else {
                    $type = 'Model';
                }
                $className = 'Pi\Application\Model\\' . $type;
            }
            $options['name'] = $key;
            $options['adapter'] = empty($options['adapter'])
                                ? $this->adapter() : $options['adapter'];
            $model = new $className($options);
            if (!$model instanceof AbstractTableGateway) {
                $model = false;
            }

            $this->model[$name] = $model;
        }

        return $this->model[$name];
    }

    /**
     * Creates Metadata
     *
     * @return Metadata
     */
    public function metadata()
    {
        if (!$this->metadata) {
            $this->metadata = new Metadata($this->getAdapter());
        }

        return $this->metadata;
    }

    /**
     * Creates `Where` object
     *
     * @param string|array|null $predicate
     * @return Where
     */
    public function where($predicate = null)
    {
        return new Where($predicate);
    }

    /**
     * Creates a SQL expression
     *
     * @param string        $expression
     * @param string|array  $parameters
     * @param array         $types
     * @return Expression
     */
    public function expression(
        $expression = '',
        $parameters = null,
        array $types = array()
    ) {
        $expression = new Expression($expression, $parameters, $types);

        return $expression;
    }

    /**
     * Log a query information or load all log information
     *
     * @param DbProfiler|null $profiler
     * @return DbProfiler|self
     */
    public function profiler(DbProfiler $profiler = null)
    {
        if (null === $profiler) {
            if (null === $this->profiler) {
                $this->profiler = Pi::service()->hasService('log')
                                ? Pi::service('log')->dbProfiler() : false;
            }

            return $this->profiler;
        }
        $this->profiler = $profiler;

        return $this;
    }

    /**
     * Create SQL
     *
     * @param Adapter $adapter
     * @param string  $table
     *
     * @return Sql
     */
    public function sql(Adapter $adapter = null, $table = '')
    {
        $sql = new Sql($adapter ?: $this->getAdapter(), $table);

        return $sql;
    }

    /**
     * Create select SQL
     *
     * @param string  $table
     *
     * @return Select
     */
    public function select($table = '')
    {
        $sql = new Select($table);

        return $sql;
    }

    /**
     * Create insert SQL
     *
     * @param string $table
     *
     * @return Insert
     */
    public function insert($table = '')
    {
        $sql = new insert($table);

        return $sql;
    }

    /**
     * Create update SQL
     *
     * @param string $table
     *
     * @return Update
     */
    public function update($table = '')
    {
        $sql = new update($table);

        return $sql;
    }

    /**
     * Create delete SQL
     *
     * @param string $table
     *
     * @return Delete
     */
    public function delete($table = '')
    {
        $sql = new delete($table);

        return $sql;
    }

    /**
     * Execute a sql query
     *
     * @param Sql|Select|Update|Delete|string $sql
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function query($sql)
    {
        if (is_string($sql)) {
            try {
                $result = $this->getAdapter()->query(
                    $sql,
                    Adapter::QUERY_MODE_EXECUTE
                );
            } catch (\Exception $e) {
                $result = false;
            }

            return $result;
        }

        try {
            $statement = $this->sql()->prepareStatementForSqlObject($sql);
        } catch (\Exception $e) {
            return false;
        }
        try {
            $result = $statement->execute();
        } catch (\Exception $e) {
            $result =  false;
        }

        return $result;
    }
}
