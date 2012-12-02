<?php
/**
 * Pi Application Db API class
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
 * @since           3.0
 * @package         Pi\Db
 * @version         $Id$
 */

namespace Pi\Application;

use Pi;
use Pi\Db\Sql\Where;
//use Pi\Db\Adapter\Driver\Statement;
use Zend\Db\Adapter\Adapter;
//use Zend\Db\Adapter\Driver\Pdo\Pdo as Driver;
//use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;
use Zend\Db\Metadata\Metadata;
use Pi\Db\Table\AbstractTableGateway;
use Pi\Log\DbProfiler;

/*
 * In installation sql scripts, quote all database names with '{' and '}' so that all names can be easily prefixed on installation.
 */
class Db
{
    /**
     * Custom statement class for \PDO
     * @see
     */
    const STATEMENT_CLASS = 'Pi\Db\Adapter\Driver\Statement';

    /**
     * Connection mode: Master-Slave, Single
     */
    //protected $mode = '';

    /**
     * Database schema
     * @var string
     */
    protected $schema;

    /**
     * Driver adapter
     * @var Adapter
     */
    protected $adapter;

    /**
     * Master-Slave adapters
     * @var array
     */
    protected $adapterMasterSlave = array(
        'master'    => null,
        'slave'     => null
    );

    /**
     * Database metadata adapter
     * @var Metadata
     */
    protected $metadata;

    /**
     * Loaded models
     * @var array
     */
    protected $model = array();

    /**
     * Name prefix for all tables
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Name prefix for system tables
     * @var string
     */
    protected $corePrefix = 'core_';

    /**
     * DB profiling logger
     * @var DbProfiler
     */
    protected $profiler;

    /**
     * Constructor
     *
     * @param array $options
     *              'connection' - Database connection parameters
     *                  Single DB mode:
     *                      'driver', 'dsn', 'username', 'password', 'options'
     *                  Master-Slave mode:
     *                      'master', 'slave'
     *              'table_prefix' - database table prefix;
     *              'core_prefix' - core table prefix
     */
    public function __construct($options)
    {
        /*
        if (isset($options['schema'])) {
            $this->setSchema($options['schema']);
            $options['connection']['dsn'] .= ';dbname=' . $options['schema'];
        }
        */
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
     * @return Db
     */
    public function loadAdapter($options)
    {
        if (isset($options['master'])) {
            $adapterMaster = $this->createAdapter($options['master']);
            $adapterSlave = $this->createAdapter($options['slave']);
            $this->setAdapter($adapterMaster, 'master')->setAdapter($adapterSlave, 'slave');
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
     * @return Db
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
     * @return Db
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
     * @return Db
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
    public function schema()
    {
        return $this->getSchema();
    }

    /**
     * Create adapter with configs
     *
     * @param array $config
     * @param \Zend\Db\Platform\PlatformInterface $platform
     * @return Adapter
     */
    public function createAdapter(array $config, $platform = null)
    {
        // Set up custom statement class
        // @see Pi\Db\Adapter\Driver\Statement
        if (!isset($config['driver_options']) && isset($config['options'])) {
            $config['driver_options'] = $config['options'];
            unset($config['options']);
        }
        if (!isset($config['driver_options'][\PDO::ATTR_STATEMENT_CLASS])) {
            $config['driver_options'][\PDO::ATTR_STATEMENT_CLASS] = array(static::STATEMENT_CLASS, array($this->profiler()));
        }
        //$driver = $this->createDriver($config);
        $adapter = new Adapter($config, $platform);
        return $adapter;
    }

    /**
     * Set adapter
     *
     * @param Adapter $adapter
     * @param null|string $type master or slave, default as null
     * @return Db
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
     * @param null|string $type master or slave, default as null
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
     * @param null|string $type master or slave, default as null
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
        $typePrefix = empty($type) || $type == 'core' ? $this->corePrefix : $type . '_';
        return sprintf('%s%s%s', $this->tablePrefix, $typePrefix, $table);
    }

    /**
     * Loads a model
     *
     * Load a normal model: Pi::db()->model('block');
     * Load a model with no model class: Pi::db()->model('page');
     * Load a nest model with no model class: Pi::db()->model('test', array('type' => 'nest'));
     *
     * @param string $name
     * @param array $options
     * @return AbstractTableGateway
     */
    public function model($name, $options = array())
    {
        $name = strtolower($name);

        if (!isset($this->model[$name])) {
            $pos = \strpos($name, '/');
            if ($pos) {
                list($module, $key) = \explode('/', $name, 2);
            } else {
                $module = '';
                $key = $name;
            }
            $className = str_replace(' ', '\\', ucwords(str_replace('_', ' ', $key)));
            if ($module) {
                $className = sprintf('Module\\%s\\Model\\%s', ucfirst($module), $className);
                $options['prefix'] = static::prefix('', $module);
            } else {
                $className = sprintf('Pi\\Application\\Model\\%s', $className);
                $options['prefix'] = static::prefix('', 'core');
            }
            if (!class_exists($className)) {
                if  (isset($options['type'])) {
                    $type = ucfirst($options['type']);
                    unset($options['type']);
                } else {
                    $type = 'Model';
                }
                $className = 'Pi\\Application\\Model\\' . $type;
            }
            $options['name'] = $key;
            $options['adapter'] = empty($options['adapter']) ? $this->adapter() : $options['adapter'];
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
     * Creates Where object
     *
     * @param string|array|null $params
     * @return Where
     */
    public function where($predicate = null)
    {
        return new Where($predicate);
    }

    /**
     * Creates a SQL expression
     *
     * @param string $expression
     * @param string|array $parameters
     * @param array $types
     * @return Expression
     */
    public function expression($expression = '', $parameters = null, array $types = array())
    {
        $expression = new Expression($expression, $parameters, $types);
        return $expression;
    }

    /**
     * Log a query information or load all log information
     *
     * @param object|null $profiler
     * @return object|Db
     */
    public function profiler($profiler = null)
    {
        if (null === $profiler) {
            if (null === $this->profiler) {
                $this->profiler = Pi::service()->hasService('log') ? Pi::service('log')->dbProfiler() : false;
            }
            return $this->profiler;
        }
        $this->profiler = $profiler;
        return $this;
    }
}
