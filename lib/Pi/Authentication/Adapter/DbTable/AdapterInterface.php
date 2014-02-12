<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication\Adapter\DbTable;

use Pi;
use Pi\Authentication\Adapter\AdapterInterface as BaseInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Pi authentication DbTable adapter interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
interface AdapterInterface extends BaseInterface
{
    /**
     * Set Db adapter
     *
     * @param DbAdapter $adapter
     * @return void
     */
    public function setDbAdapter(DbAdapter $adapter = null);
}
