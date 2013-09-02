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
class Local extends System
{
    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        $result = parent::get($name);
        if (null === $result && 'id' != $name) {
            $uid = $this->get('id');
            if ($uid) {
                $result = Pi::api('user', 'user')->get($uid, $name);
                $this->data[$name] = $result;
            }
        }

        return $result;
    }
}