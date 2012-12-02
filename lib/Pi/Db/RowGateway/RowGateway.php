<?PHP
/**
 * Pi Row Gateway
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
 * @subpackage      RowGateway
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Db\RowGateway;

use Zend\Db\RowGateway\AbstractRowGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\RowGateway\Feature;
use Zend\Db\RowGateway\Exception;

/**
 * Use 'encode' to serialize array and object data before saveing to database and use 'decode' after fetching from database
 *
 * $data: regular data
 * $primayKeyData: primary key value(s)
 * $originalData: data for direct in/out DB
 */

class RowGateway extends AbstractRowGateway
{
    /**
     * Primary key column, scalar, different from native ZF2 property
     * @var string
     */
    protected $primaryKeyColumn = null;

    /**
     * Primay key columns, plural, equal to ZF2's $primaryKeyColumn
     * @var array
     */
    protected $primaryKeyColumns = array();

    /**
     * @var array
     */
    //protected $primaryKeyData = null;

    /**#@+
     * The originalData and data are declared in \Zend\Db\RowGateway\AbstractRowGateway with no informative explanation.
     * My guess is: originalData for date fetched from DB directory without processing; data for assigned from applications
     */
    /**
     * @var array
     */
    //protected $originalData = null;

    /**
     * @var array
     */
    //protected $data = array();
    /**#@-*/

    /**
     * Non-scalar columns to be endcoded/decoded during save/fetch with DB
     * @var type
     */
    protected $encodeColumns = array(
        // column name => convert to associative array?
        //'col_array'     => true,
        //'col_object'    => false,
    );

    /**
     * Constructor
     *
     * @param string $primaryKeyColumn
     * @param string|\Zend\Db\Sql\TableIdentifier $table
     * @param Adapter|Sql $adapterOrSql
     */
    public function __construct($primaryKeyColumn, $table, $adapterOrSql = null)
    {
        // setup primary key
        $this->primaryKeyColumn = $primaryKeyColumn ?: $this->primaryKeyColumn;

        // set table
        $this->table = $table;

        // set Sql object
        if ($adapterOrSql instanceof Sql) {
            $this->sql = $adapterOrSql;
        } elseif ($adapterOrSql instanceof Adapter) {
            $this->sql = new Sql($adapterOrSql, $this->table);
        } else {
            throw new Exception\InvalidArgumentException('A valid Sql object was not provided.');
        }

        if ($this->sql->getTable() !== $this->table) {
            throw new Exception\InvalidArgumentException('The Sql object provided does not have a table that matches this row object');
        }

        $this->initialize();
    }

    /**
     * initialize()
     */
    public function initialize()
    {
        if ($this->isInitialized) {
            return;
        }

        if (!$this->featureSet instanceof Feature\FeatureSet) {
            $this->featureSet = new Feature\FeatureSet;
        }

        $this->featureSet->setRowGateway($this);
        $this->featureSet->apply('preInitialize', array());

        if (!is_string($this->table) && !$this->table instanceof TableIdentifier) {
            throw new Exception\RuntimeException('This row object does not have a valid table set.');
        }

        if ($this->primaryKeyColumn == null) {
            throw new Exception\RuntimeException('This row object does not have a primary key column set.');
        /*
        } elseif (is_string($this->primaryKeyColumn)) {
            $this->primaryKeyColumn = (array) $this->primaryKeyColumn;
        */
        } elseif (is_string($this->primaryKeyColumn)) {
            $this->primaryKeyColumns = (array) $this->primaryKeyColumn;
        } elseif (is_array($this->primaryKeyColumn)) {
            $this->primaryKeyColumns = $this->primaryKeyColumn;
            $this->primaryKeyColumn = null;
        }

        if (!$this->sql instanceof Sql) {
            throw new Exception\RuntimeException('This row object does not have a Sql object set.');
        }

        $this->featureSet->apply('postInitialize', array());

        $this->isInitialized = true;
    }

    /**#@+
     * Pi Engine methods for column encode/decode
     */
    /**
     * Set columns to be encode/decode
     *
     * @param array $columns
     * @return RowGateway
     */
    public function setEncodeColumns(array $columns)
    {
        $this->encodeColumns = $columns;
        return $this;
    }

    public function getEncodeColumns()
    {
        return $this->encodeColumns;
    }

    /**
     * Encode content
     *
     * @param  array|resource|object $value
     * @return mixed
     */
    protected function encodeValue($value)
    {
        return $value ? json_encode($value) : '';
    }

    /**
     * Decode content
     *
     * @param string $value
     * @param bool $assoc
     * @return array|resource|object
     */
    protected function decodeValue($value, $assoc = true)
    {
        return $value ? json_decode($value, $assoc) : '';
    }

    /**
     * Encode non-scalar columns
     *
     * @param array $data
     * @return array
     */
    protected function encode($data)
    {
        foreach (array_keys($this->encodeColumns) as $column) {
            if (array_key_exists($column, $data)) {
                // Escape if already a scalar
                if (is_scalar($data[$column])) {
                    continue;
                }
                $data[$column] = $this->encodeValue($data[$column]);
            }
        }

        return $data;
    }

    /**
     * Decode non-scalar columns
     *
     * @param array $data
     * @return array
     */
    public function decode($data)
    {
        foreach ($this->encodeColumns as $column => $assoc) {
            if (array_key_exists($column, $data)) {
                // Escape if already a non-scalar
                if (!is_scalar($data[$column])) {
                    break;
                }
                $data[$column] = $this->decodeValue($data[$column], $assoc);
            }
        }

        return $data;
    }

    /**
     * Encode a non-scalar column
     * @param string $column Column/field name
     * @return string
     */
    public function encodeColumn($column)
    {
        return $this->encodeValue($this->data[$column]);
    }

    /**
     * Decode a non-scalar column
     *
     * @param string $column Column/field name
     * @param bool $assoc
     * @return array|object|resoure
     */
    public function decodeColumn($column, $assoc = true)
    {
        return $this->decodeValue($this->data[$column], $assoc);
    }
    /**#@-*/

    /**
     * Populate Data
     *
     * @param  array $currentData
     * @return RowGateway
     */
    public function populate(array $rowData, $rowExistsInDatabase = false)
    {
        $this->initialize();

        //$this->data = $rowData;
        if ($rowExistsInDatabase == true) {
            $this->data = $this->decode($rowData);
            $this->processPrimaryKeyData();
        } else {
            $this->primaryKeyData = null;
            $this->data = $rowData;
        }

        return $this;
    }

    /**
     * Save
     *
     * @param bool $rePopulate
     * @return integer
     */
    public function save($rePopulate = true)
    {
        $this->initialize();

        /**#@+
            * Encode data to make it db-ready
            */
        $this->data = $this->encode($this->data);
        /**#@-*/

        if ($this->rowExistsInDatabase()) {

            // UPDATE

            $data = $this->data;
            $where = array();

            // primary key is always an array even if its a single column
            foreach ($this->primaryKeyColumns as $pkColumn) {
                $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
                if ($data[$pkColumn] == $this->primaryKeyData[$pkColumn]) {
                    unset($data[$pkColumn]);
                }
            }

            $statement = $this->sql->prepareStatementForSqlObject($this->sql->update()->set($data)->where($where));
            $result = $statement->execute();
            $rowsAffected = $result->getAffectedRows();
            unset($statement, $result); // cleanup

        } else {

            // INSERT
            $insert = $this->sql->insert();
            $insert->values($this->data);

            $statement = $this->sql->prepareStatementForSqlObject($insert);

            $result = $statement->execute();
            if (($primaryKeyValue = $result->getGeneratedValue()) && count($this->primaryKeyColumns) == 1) {
                $this->primaryKeyData = array($this->primaryKeyColumns[0] => $primaryKeyValue);
            } else {
                // make primary key data available so that $where can be complete
                $this->processPrimaryKeyData();
            }

            $rowsAffected = $result->getAffectedRows();
            unset($statement, $result); // cleanup

            $where = array();
            // primary key is always an array even if its a single column
            foreach ($this->primaryKeyColumns as $pkColumn) {
                $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
            }

        }

        if ($rePopulate) {
            // refresh data
            $statement = $this->sql->prepareStatementForSqlObject($this->sql->select()->where($where));
            $result = $statement->execute();
            $rowData = $result->current();
            //$rowData = $result->getDatasource()->current();
            unset($statement, $result); // cleanup

            // make sure data and original data are in sync after save
            $this->populate($rowData, true);
        }

        // return rows affected
        return $rowsAffected;
    }

    /**
     * Delete
     *
     * @return type
     */
    public function delete()
    {
        $this->initialize();

        $where = array();
        // primary key is always an array even if its a single column
        foreach ($this->primaryKeyColumns as $pkColumn) {
            $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
        }

        // @todo determine if we need to do a select to ensure 1 row will be affected

        $statement = $this->sql->prepareStatementForSqlObject($this->sql->delete()->where($where));
        $result = $statement->execute();

        /*
        if ($result->getAffectedRows() == 1) {
            // detach from database
            $this->primaryKeyData = null;
        }
        */

        $result = $statement->execute();
        $affectedRows = $result->getAffectedRows();

        if ($affectedRows == 1) {
            // detach from database
            $this->primaryKeyData = null;
        }

        return $affectedRows;
    }

    public function assign($data)
    {
        foreach ($data as $offset => $value) {
            $this->offsetSet($offset, $value);
        }

        return $this;
    }

    /**
     * @throws Exception\RuntimeException
     */
    protected function processPrimaryKeyData()
    {
        $this->primaryKeyData = array();
        foreach ($this->primaryKeyColumns as $column) {
            if (!isset($this->data[$column])) {
                continue;
                throw new Exception\RuntimeException('While processing primary key data, a known key ' . $this->table . '.' . $column . ' was not found in the data array');
            }
            $this->primaryKeyData[$column] = $this->data[$column];
        }
    }
}
