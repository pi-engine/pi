<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Dashboard controller
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class DashboardController extends ActionController
{
    /**
     * Edit base user information
     *
     * @return array|void
     */
    public function indexAction()
    {
        // Check login in
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $uid = Pi::user()->getId();

        // Get identity, email, name
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('identity', 'email', 'name')
        );
        $user['uid']    = $uid;
        $user['id']     = $uid;


        /*
         * Get bookings
         */
        $bookings = null;
        $itemList = null;

        if(Pi::service('module')->isActive('guide')){

            $owner = Pi::model('guide/owner')->find($uid, 'uid');

            $guideModel = Pi::model('guide/item');
            $where = array('owner' => $owner->id, 'item_type' => 'commercial');
            $order = array('id DESC');
            // Get list of item


            $select = $guideModel->select()->where($where)->order($order);

            $rowset = $guideModel->selectWith($select);

            $items = array();
            $itemList = array();
            foreach ($rowset as $row) {
                $item = Pi::api('item', 'guide')->canonizeItemLight($row);
                $items[] = $row->id;
                $itemList['commercial'][$row->id] = $item;
            }

            if (count($items)) {
                $bookings = Pi::api('booking', 'guide')->getActualBookings($items, array('status' => \Module\Guide\Model\Booking::STATUS_PENDING));
            }
        }

        $this->view()->assign(array(
            'user'      => $user,
            'bookings'   => $bookings,
            'list' => $itemList,
        ));

        $this->view()->headTitle(__('Account settings'));
        $this->view()->headdescription(__('Basic settings'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }
}