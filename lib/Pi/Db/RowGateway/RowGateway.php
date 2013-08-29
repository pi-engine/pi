<?PHP
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Db\RowGateway;

use Zend\Db\RowGateway\RowGateway as AbstractRowGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\RowGateway\Feature;
use Zend\Db\RowGateway\Exception;

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
     * Non-scalar columns to be encoded before saving to DB
     * and decoded after fetching from DB,
     * specified as pairs of column name and bool value:
     * true - to convert to associative array for decode;
     * false - keep as array object.
     * @var array
     */
    protected $encodeColumns = array();

    /**
     * Constructor
     *
     * @param string                              $primaryKeyColumn
     * @param string|\Zend\Db\Sql\TableIdentifier $table
     * @param Adapter|Sql                         $adapterOrSql
     *
     * @return \Pi\Db\RowGateway\RowGateway
     */
    public function __construct(
        $primaryKeyColumn,
        $table,
        $adapterOrSql = null
    ) {
        // setup primary key
        $this->primaryKeyColumn = $primaryKeyColumn ?: $this->primaryKeyColumn;
        $this->pkColumn = $this->primaryKeyColumn;

        parent::__construct($this->primaryKeyColumn, $table, $adapterOrSql);
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
        $value = $value ?: array();

        return json_encode($value);
    }

    /**
     * Decode content
     *
     * @param string    $value
     * @param bool      $assoc
     * @return array|resource|object
     */
    protected function decodeValue($value, $assoc = true)
    {
        return $value
            ? json_decode($value, $assoc) : ($assoc ? array() : $value);
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
     * @param bool  $rowExistsInDatabase If row is already in DB
     * @return $this
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
     * Save a row
     *
     * @param bool $rePopulate  To re-populate data
     * @return int
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
            foreach ($this->primaryKeyColumn as $pkColumn) {
                $where[$pkColumn] = $this->primaryKeyData[$pkColumn];
                if ($data[$pkColumn] == $this->primaryKeyData[$pkColumn]) {
                    unset($data[$pkColumn]);
                }
            }

            $statement = $this->sql->prepareStatementForSqlObject(
                $this->sql->update()->set($data)->where($where)
            );
            $result = $statement->execute();
            $rowsAffected = $result->getAffectedRows();
            unset($statement, $result); // cleanup

        } else {

            // INSERT
            $insert = $this->sql->insert();
            $insert->values($this->data);

            $statement = $this->sql->prepareStatementForSqlObject($insert);

            $result = $statement->execute();
            if (($primaryKeyValue = $result->getGeneratedValue())
                && count($this->primaryKeyColumn) == 1
            ) {
                $this->primaryKeyData = array(
                    $this->primaryKeyColumn[0] => $primaryKeyValue
                );
            } else {
                // make primary key data available so that
                // $where can be complete
                $this->processPrimaryKeyData();
            }

            $rowsAffected = $result->getAffectedRows();
            unset($statement, $result); // cleanup

            $where = array();
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
            $result = $statement->execute();
            $rowData = $result->current();
            unset($statement, $result); // cleanup

            // make sure data and original data are in sync after save
            $this->populate($rowData, true);
        }

        // return rows affected
        return $rowsAffected;
    }

    /**
     * Assign data
     *
     * @param array $data
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
     * Process primary key
     *
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function processPrimaryKeyData()
    {
        $this->primaryKeyData = array();
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
