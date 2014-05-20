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

/**
 * User profile compound manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Compound extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /**
     * Get meta of a compound
     *
     * @param string $name
     *
     * @return array
     */
    public function getMeta($name)
    {
        $list = Pi::registry('field', 'user')->read('compound', 'display');
        $result = isset($list[$name]) ? $list[$name] : array();

        return $result;
    }

    /**
     * Get user custom compound/field
     *
     * @param int|int[]   $uid
     * @param string $name Compound name
     * @param bool  $filter     To filter for display
     *
     * @return array
     */
    public function get($uid, $name, $filter = false)
    {
        $uids = (array) $uid;
        $result = $this->mget($uids, $name, $filter);
        if (is_scalar($uid)) {
            $result = isset($result[$uid]) ? $result[$uid] : array();
        }

        return $result;
    }

    /**
     * Get multiple user custom compound fields
     *
     * @param int[] $uids
     * @param string $name Compound name
     * @param bool  $filter     To filter for display
     *
     * @return array
     */
    public function mget($uids, $name, $filter = false)
    {
        $result = array();
        $meta = $this->getMeta($name);
        if (!$meta) {
            return $result;
        }

        if ($meta['handler']) {
            $handler    = new $meta['handler']($name);
            $result     = $handler->mget($uids, $filter);
        } else {
            $model = Pi::model('compound', 'user');
            $select = $model->select();
            $select->order('set ASC')->where(array(
                'uid'       => $uids,
                'compound'  => $name,
            ));
            $rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                if ($filter) {
                    $value = $row->filter();
                } else {
                    $value = $row['value'];
                }
                $id         = (int) $row['uid'];
                $set        = (int) $row['set'];
                $var        = $row['field'];
                $result[$id][$set][$var] = $value;
            }
        }

        return $result;
    }

    /**
     * Get compound data for display
     *
     * @param int|int[] $uid
     * @param string $name Compound name
     * @param array|null $data
     *
     * @return array
     */
    public function display($uid, $name, $data = null)
    {
        $result = array();

        if (null === $data) {
            $data = $this->get($uid, $name);
        }
        $meta = $this->getMeta($name);
        if (!$meta) {
            return $result;
        }
        if ($meta['handler']) {
            $handler    = new $meta['handler']($name);
            $result     = $handler->display($uid, $data);
        } else {
            if (is_scalar($uid)) {
                $data = array($uid => $data);
            }
            $meta = Pi::registry('compound_field', 'user')->read($name);
            array_walk($data, function (&$list) use ($meta) {
                $temp = $list;
                $list = array();
                foreach ($temp as $item) {
                    $record = array();
                    foreach ($meta as $name => $field) {
                        if (!isset($item[$name])) {
                            continue;
                        }
                        $record[$name] = array(
                            'title' => $field['title'],
                            'value' => $item[$name],
                        );
                    }
                    $list[] = $record;
                }
            });

            if (is_scalar($uid)) {
                $result = isset($data[$uid]) ? $data[$uid] : array();
            } else {
                $result = $data;
            }
        }

        return $result;
    }
}