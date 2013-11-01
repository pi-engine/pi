<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Model;

use Pi;

/**
 * Local user model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Client extends System
{
    /**
     * {@inheritDoc}
     */
    public function load($uid, $field = 'id')
    {
        if ($uid) {
            if ('id' == $field) {
                $data = (array) Pi::service('user')->get($uid);
            } else {
                $list = Pi::service('user')->getList(array($field => $uid), 1);
                $data = array_values($list);
                $data = array_pop($data);
            }
        } else {
            $data = array('id' => 0);
        }
        $this->assign($data);

        return $this;
    }
}