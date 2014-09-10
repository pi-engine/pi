<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $meta = Pi::registry('field', 'user')->read('profile');
        $result = isset($meta[$this->getName()])
            ? $meta[$this->getName()] : array();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function canonize($uid, $data)
    {
        $result = array(
            'uid'   => $uid,
            'value' => $data,
        );

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
            $select->where(array('uid' => $uid));
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[] = $row['value'];
            }
        } else {
            $row = $this->getModel()->find($uid, 'uid');
            $result = $row ? $row['value'] : null;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function mget($uids, $filter = false)
    {
        $result = array();
        $select = $this->getModel()->select();
        $select->where(array('uid' => $uids));

        if ($this->isMultiple) {
            $select->order('order ASC');
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[(int) $row['uid']][] = $row['value'];
            }
        } else {
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[(int) $row['uid']] = $row['value'];
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
            $uids = (array) $uid;
            $data = array($uid => $data);
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
            $data = isset($data[$uid]) ? $data[$uid] : array();
        }

        return $data;
    }
}
