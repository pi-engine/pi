<?PHP
/**
 * Pi Master-Slave Table Gateway
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
use Pi;
use Pi\Application\Db;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Metadata\Metadata;

class MasterSlaveTableGateway extends AbstractTableGateway
{
    /**#@+
     * Master-Slave
     */
    /**
     * @var Adapter
     */
    protected $masterAdapter = null;

    /**
     * @var Adapter
     */
    protected $slaveAdapter = null;
    /**#@-*/

    public function initialize()
    {
        if ($this->initialized == true) {
            return;
        }

        if (!$this->masterAdapter && !$this->slaveAdapter) {
            throw new \Exception('Master/Slave adapters must be configured in initialize()');
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
     * Select
     *
     * @param  string $where
     * @return type
     */
    public function select($where = null)
    {
        $this->adapter = $this->slaveAdapter;
        return parent::select($where);
    }

    /**
     * Insert
     *
     * @param  string $set
     * @return type
     */
    public function insert($set)
    {
        $this->adapter = $this->masterAdapter;
        return parent::insert($set);
    }

    /**
     * Update
     *
     * @param  string $set
     * @param  string $where
     * @return type
     */
    public function update($set, $where = null)
    {
        $this->adapter = $this->masterAdapter;
        return parent::update($set, $where);
    }

    /**
     * Delete
     *
     * @param  string $where
     * @return type
     */
    public function delete($where)
    {
        $this->adapter = $this->masterAdapter;
        return parent::delete($where);
    }
}
