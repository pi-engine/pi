<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\Db\Sql\Where;
use Pi\User\Model\Local as UserModel;

/**
 * Cgu API
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class Condition extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /** @var string Route for user URLs */
    protected $route = 'user';

    /**
     * Get conditions list, order by created_at date
     * @return null|\Zend\Db\ResultSet\ResultSetInterface
     */
    public function getConditionList()
    {
        // Get info
        $order = array('created_at DESC');
        $select = Pi::model('condition', $this->getModule())->select()->order($order);
        $rowset = Pi::model('condition', $this->getModule())->selectWith($select);

        return $rowset;
    }

    /**
     * Remove condition
     * @param $id
     * @return boolean
     */
    public function removeCondition($id)
    {
        $condition = Pi::model('condition', 'user')->find($id);

        if($condition){
            $condition->delete();
            return true;
        } else {
            return false;
        }
    }
}
