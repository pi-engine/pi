<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Field;

use Pi;
use Pi\Application\Installer\SqlSchema;
use Pi\Db\Table\AbstractTableGateway;
use Pi\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;

/**
 * Abstract class for scalar custom field handling
 *
 * {@inheritDoc}
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class CustomFieldHandler extends AbstractCustomHandler
{
    /**
     * {@inheritDoc}
     */
    public function getMeta()
    {
        $meta   = Pi::registry('field', 'user')->read('profile');
        $result = isset($meta[$this->getName()])
            ? $meta[$this->getName()] : [];

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function canonize($uid, $data)
    {
        $result = [
            'uid'   => $uid,
            'value' => $data,
        ];

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function get($uid, $filter = false)
    {
        $result = null;
        if ($this->isMultiple) {
            $select = $this->getModel()->select();
            $select->order('order ASC');
            $select->where(['uid' => $uid]);
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[] = $row['value'];
            }
        } else {
            $row    = $this->getModel()->find($uid, 'uid');
            $result = $row ? $row['value'] : null;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function mget($uids, $filter = false)
    {
        $result = [];
        $select = $this->getModel()->select();
        $select->where(['uid' => $uids]);

        if ($this->isMultiple) {
            $select->order('order ASC');
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[(int)$row['uid']][] = $row['value'];
            }
        } else {
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[(int)$row['uid']] = $row['value'];
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function display($uid, $data = null)
    {
        if (is_scalar($uid)) {
            $uids = (array)$uid;
            $data = [$uid => $data];
        } else {
            $uids = $uid;
        }
        if (null === $data) {
            $data = $this->mget($uids, true);
        }

        array_walk($data, function (&$item) {
            if ($this->isMultiple) {
                $item = implode(' | ', $item);
            }
        });

        if (is_scalar($uid)) {
            $data = isset($data[$uid]) ? $data[$uid] : [];
        }

        return $data;
    }
}
