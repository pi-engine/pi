<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Custom\User\Field;

use Pi;
use Module\User\CustomFieldHandler;

/**
 * Custom interest handler
 *
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Interest extends CustomFieldHandler
{
    /** @var string Field name and table name */
    protected $name = 'interest';

    protected function lookup(array $data)
    {
        // Key => Value
        $interestMap = array();

        $result = array();
        foreach ($data as $value) {
            if (isset($interestMap[$value])) {
                $list[] = $interestMap[$value];
            } else {
                $list[] = $value;
            }
        }

        return $result;
    }
    /**
     * {@inheritDoc}
     */
    public function get($uid, $filter = false)
    {
        $data = parent::get($uid);
        if ($filter) {
            $list = $this->lookup($data);
            $result = implode(' ', $list);
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function mget($uids, $filter = false)
    {
        $data = parent::mget($uids);
        if ($filter) {
            foreach ($data as $uid => $uData) {
                $list = $this->lookup($uData);
                $result[$uid] = implode(' ', $list);
            }
        } else {
            $result = $data;
        }

        return $result;
    }
}
