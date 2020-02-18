<?PHP
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Db\RowGateway;

use Pi;
use Pi\Db\Table\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\RowGateway\Exception;
use Zend\Db\RowGateway\RowGateway as AbstractRowGateway;
use Zend\Db\Sql\Sql;

/**
 * Row gateway class
 *
 * Use 'encode' to serialize array and object data before saveing
 * to database and use 'decode' after fetching from database
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RowGateway extends AbstractRowGateway
{
    /**
     * Primary key column, scalar
     * @var string
     */
    protected $pkColumn = null;

    /**
     * Model
     *
     * @var AbstractTableGateway
     */
    protected $model;

    protected $_oldData = array();

    /**
     * Table fields/columns.
     *
     * @var string[]
     */
    protected $columns = [];

    /**
     * Non-scalar columns to be encoded before saving to DB
     * and decoded after fetching from DB,
     * specified as pairs of column name and bool value:
     * true - to convert to associative array for decode;
     * false - keep as array object.
     * @var array
     */
    protected $encodeColumns = [];

    /**
     * Constructor
     *
     * @param string $primaryKeyColumn
     * @param string|AbstractTableGateway|\Zend\Db\Sql\TableIdentifier $table
     * @param Adapter|Sql $adapterOrSql
     *
     * @return \Pi\Db\RowGateway\RowGateway
     */
    public function __construct(
        $primaryKeyColumn,
        $table,
        $adapterOrSql = null
    )
    {
        // setup primary key
        $this->primaryKeyColumn = $primaryKeyColumn ?: $this->primaryKeyColumn;
        $this->pkColumn         = $this->primaryKeyColumn;
        if ($table instanceof AbstractTableGateway) {
            $this->setModel($table);
            $table = $table->getTable();
        }

        parent::__construct($this->primaryKeyColumn, $table, $adapterOrSql);
    }

    /**
     * Set model
     *
     * @param AbstractTableGateway $model
     *
     * @return $this
     */
    public function setModel(AbstractTableGateway $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return AbstractTableGateway
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set columns
     *
     * @param array $columns
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get column names
     *
     * @param bool $fetch Fetch from metadata if not specified
     *
     * @return string[]
     */
    public function getColumns($fetch = false)
    {
        if (!$this->columns && $this->model) {
            $columns = $this->model->getColumns($fetch);
        } else {
            $columns = $this->columns;
        }

        return $columns;
    }

    /**#@+
     * Pi Engine methods for column encode/decode
     */
    /**
     * Set columns to be encode/decode
     *
     * @param array $columns
     * @return $this
     */
    public function setEncodeColumns(array $columns)
    {
        $this->encodeColumns = $columns;

        return $this;
    }

    /**
     * Get columns to be encoded/decoded
     *
     * @return array
     */
    public function getEncodeColumns()
    {
        return $this->encodeColumns;
    }

    /**
     * Encode content
     *
     * @param  array|resource|object $value
     * @return string
     */
    protected function encodeValue($value)
    {
        $value = $value ?: [];

        return json_encode($value);
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
        return $value
            ? json_decode($value, $assoc) : ($assoc ? [] : $value);
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
                /*
                if (is_scalar($data[$column])) {
                    continue;
                }
                */
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
                // Skip if already a non-scalar
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
     *
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
     * @return array|object|resource
     */
    public function decodeColumn($column, $assoc = true)
    {
        return $this->decodeValue($this->data[$column], $assoc);
    }
    /**#@-*/

    /**
     * Populate Data
     *
     * @param array $rowData
     * @param bool $rowExistsInDatabase If row is already in DB
     * @return $this
     */
    public function populate(array $rowData, $rowExistsInDatabase = false)
    {
        $this->initialize();

        if ($rowExistsInDatabase == true) {
            $this->data = $this->decode($rowData);
            $this->processPrimaryKeyData();
        } else {
            $this->primaryKeyData = null;
            $this->data           = $rowData;
        }

        return $this;
    }

    /**
     * Save a row
     *
     * @param bool $rePopulate To re-populate data
     * @param bool $filter Filter invalid columns
     *
     * @return int
     */
    public function save($rePopulate = true, $filter = true)
    {
        $this->initialize();

        /**#@+
         * Encode data to make it db-ready
         */
        $this->data = $this->encode($this->data);
        if ($filter) {
            $columns = $this->columns ?: $this->model->getColumns();
            if ($columns) {
                foreach (array_keys($this->data) as $column) {
                    if (!in_array($column, $columns)) {
                        unset($this->data[$column]);
                    }
                }
            }
        }
        /**#@-*/

        /**
         * Force null value for empty media
         */
        $model = $this->getModel();
        if (Pi::service('module')->isActive('media') && $model instanceof \Pi\Application\Model\Model && $mediaLinks = $model->getMediaLinks()) {

            foreach ($mediaLinks as $key) {
                if (isset($this->data[$key]) && $this->data[$key] == '') {
                    $this->data[$key] = null;
                }
            }
        }

        $wasExisting = true;

        if ($this->rowExistsInDatabase()) {

            // UPDATE

            $data  = $this->data;
            $where = [];

            // primary key is always an array even if its a single column
            foreach ($this->primaryKeyColumn as $pkColumn) {
                $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
                if ($data[$pkColumn] == $this->primaryKeyData[$pkColumn]) {
                    unset($data[$pkColumn]);
                }
            }

            $statement    = $this->sql->prepareStatementForSqlObject(
                $this->sql->update()->set($data)->where($where)
            );
            $result       = $statement->execute();
            $rowsAffected = $result->getAffectedRows();
            unset($statement, $result); // cleanup

        } else {

            $wasExisting = false;

            // INSERT
            $insert = $this->sql->insert();
            $insert->values($this->data);

            $statement = $this->sql->prepareStatementForSqlObject($insert);

            $result = $statement->execute();
            if (($primaryKeyValue = $result->getGeneratedValue())
                && count($this->primaryKeyColumn) == 1
            ) {
                $this->primaryKeyData = [
                    $this->primaryKeyColumn[0] => $primaryKeyValue,
                ];
            } else {
                // make primary key data available so that
                // $where can be complete
                $this->processPrimaryKeyData();
            }

            $rowsAffected = $result->getAffectedRows();
            unset($statement, $result); // cleanup

            $where = [];
            // primary key is always an array even if its a single column
            foreach ($this->primaryKeyColumn as $pkColumn) {
                $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
            }
        }

        if ($rePopulate) {
            // refresh data
            $statement = $this->sql->prepareStatementForSqlObject(
                $this->sql->select()->where($where)
            );
            $result    = $statement->execute();
            $rowData   = $result->current();
            unset($statement, $result); // cleanup

            // make sure data and original data are in sync after save
            $this->populate($rowData, true);
        }

        /**
         * Add trigger event for module observers
         */
        if (!$wasExisting) {
            Pi::service('observer')->triggerInsertedRow($this);
        } else {
            Pi::service('observer')->triggerUpdatedRow($this, $this->_oldData);
        }

        /**
         * Media management
         */
        $model = $this->getModel();
        if (Pi::service('module')->isActive('media') && $model instanceof \Pi\Application\Model\Model && $model->getMediaLinks()) {
            Pi::api('link', 'media')->updateLinks($this);
        }

        // return rows affected
        return $rowsAffected;
    }

    public function delete()
    {
        $affectedRows = parent::delete();

        if ($affectedRows == 1 && Pi::service('module')->isActive('media')) {
            $model = $this->getModel();
            if ($model instanceof \Pi\Application\Model\Model && $model->getMediaLinks()) {
                Pi::api('link', 'media')->removeLinks($this);
            }
        }

        return $affectedRows;
    }

    /**
     * Assign data
     *
     * @param array $data
     *
     * @return $this
     */
    public function assign($data)
    {
        foreach ($data as $offset => $value) {
            $this->offsetSet($offset, $value);
        }

        return $this;
    }

    /**
     * Offset set
     *
     * @param  string $offset
     * @param  mixed $value
     * @return RowGateway
     */
    public function offsetSet($offset, $value)
    {
        if(isset($this->_oldData) && isset($this->data[$offset])){
            $this->_oldData[$offset] = $this->data[$offset];
        }

        $this->data[$offset] = $value;
        return $this;
    }

    /**
     * Process primary key
     *
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function processPrimaryKeyData()
    {
        $this->primaryKeyData = [];
        foreach ($this->primaryKeyColumn as $column) {
            if (!isset($this->data[$column])) {
                continue;
                /*
                throw new Exception\RuntimeException(
                    'While processing primary key data, a known key '
                    . $this->table . '.' . $column
                    . ' was not found in the data array'
                );
                */
            }
            $this->primaryKeyData[$column] = $this->data[$column];
        }
    }
}
