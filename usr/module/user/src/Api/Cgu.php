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
class Cgu extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /** @var string Route for user URLs */
    protected $route = 'user';

    /**
     * Get cgu list, order by created_at date
     * @return null|\Zend\Db\ResultSet\ResultSetInterface
     */
    public function getCguList()
    {
        // Get info
        $order = array('created_at DESC');
        $select = Pi::model('cgu', $this->getModule())->select()->order($order);
        $rowset = Pi::model('cgu', $this->getModule())->selectWith($select);

        return $rowset;
    }

    /**
     * Remove Cgu
     * @param $id
     * @return boolean
     */
    public function removeCgu($id)
    {
        $cgu = Pi::model('cgu', 'user')->find($id);

        if($cgu){
            $cgu->delete();
            return true;
        } else {
            return false;
        }
    }
}
