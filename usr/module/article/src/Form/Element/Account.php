<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Element;

use Pi;
use Zend\Form\Element\Select;

/**
 * Account element class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Account extends Select
{
    /**
     * Read account from database
     * 
     * @return array 
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            // Getting children role of article module
            $rowRole = Pi::model('acl_role')->getChildren('article-manager');
            $roles   = array('article-manager');
            foreach ($rowRole as $role) {
                $roles[] = $role;
            }
            
            // Getting account ID
            $rowset = Pi::model('user_role')->select(array('role' => $roles));
            $ids    = array(0);
            foreach ($rowset as $row) {
                $ids[$row->user] = $row->user;
            }

            // Getting active account
            $rowset = Pi::user()->get($ids, array('id', 'name'));
            $account = array();
            foreach ($rowset as $row) {
                $account[$row['id']] = $row['name'];
            }
            $account = empty($account) ? array(0 => __('Null')) : $account;
            $this->valueOptions = $account;
        }

        return $this->valueOptions;
    }
}
