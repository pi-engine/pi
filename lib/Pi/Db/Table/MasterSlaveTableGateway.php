<?PHP
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Db\Table;

use Pi;
use Pi\Application\Db;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Metadata\Metadata;

/**
 *  Pi Master-Slave Table Gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class MasterSlaveTableGateway extends AbstractTableGateway
{
    /**#@+
     * Master-Slave
     */
    /** @var Adapter Master adapter */
    protected $masterAdapter = null;

    /** @var Adapter Slave adapter */
    protected $slaveAdapter = null;
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        if ($this->initialized == true) {
            return;
        }

        if (!$this->masterAdapter && !$this->slaveAdapter) {
            throw new \Exception(
                'Master/Slave adapters must be configured in initialize()'
            );
        }
        $this->adapter = $this->adapter ?: $this->slaveAdapter;

        parent::initialize();
    }

    /**
     * Get adapter
     *
     * @param string $type
     * @return Adapter
     */
    public function getAdapter($type = null)
    {
        if ('master' == $type) {
            return $this->masterAdapter;
        } elseif ('slave' == $type) {
            return $this->slaveAdapter;
        } else {
            return $this->adapter;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function select($where = null)
    {
        $this->adapter = $this->slaveAdapter;

        return parent::select($where);
    }

    /**
     * {@inheritDoc}
     */
    public function insert($set)
    {
        $this->adapter = $this->masterAdapter;

        return parent::insert($set);
    }

    /**
     * {@inheritDoc}
     */
    public function update($set, $where = null)
    {
        $this->adapter = $this->masterAdapter;

        return parent::update($set, $where);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($where)
    {
        $this->adapter = $this->masterAdapter;
        
        return parent::delete($where);
    }
}
