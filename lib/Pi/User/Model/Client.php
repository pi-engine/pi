<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
                $data = (array)Pi::service('user')->get($uid);
            } else {
                $list = Pi::service('user')->getList([$field => $uid], 1);
                if ($list) {
                    $data = array_values($list);
                    $data = array_pop($data);
                } else {
                    $data = [];
                }
            }
        } else {
            $data = $this->getGuest();
        }
        $this->assign($data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        $result = parent::get($name);
        if (null === $result && 'id' != $name) {
            $uid = $this->get('id');
            if ($uid) {
                $result            = Pi::api('user', 'uclient')->get($uid, $name);
                $this->data[$name] = $result;
            }
        }

        return $result;
    }
}