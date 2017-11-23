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
 * Abstract class for custom compound handling
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class CustomCompoundHandler extends AbstractCustomHandler
{
    /**
     * {@inheritDoc}
     */
    public function get($uid, $filter = false)
    {
        $result = [];
        if ($this->isMultiple) {
            $select = $this->getModel()->select();
            $select->order('order ASC');
            $select->where(['uid' => $uid]);
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[] = $row->toArray();
            }
        } else {
            $row      = $this->getModel()->find($uid, 'uid');
            $result[] = $row ? $row->toArray() : [];
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
        }
        $rowset = $this->getModel()->selectWith($select);
        foreach ($rowset as $row) {
            $result[(int)$row['uid']][] = $row->toArray();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function display($uid, $data = null)
    {
        $result = [];

        $meta = Pi::registry('compound_field', 'user')->read($this->name);
        if (!$meta) {
            return $result;
        }

        if (is_scalar($uid)) {
            $uids = (array)$uid;
            if (null !== $data) {
                $data = [$uid => $data];
            }
        } else {
            $uids = $uid;
        }
        if (null === $data) {
            $data = $this->mget($uids, true);
        }

        array_walk($data, function (&$list) use ($meta) {
            $list = $this->displayFields($list, $meta);
        });

        if (is_scalar($uid)) {
            $data = isset($data[$uid]) ? $data[$uid] : [];
        }

        return $data;
    }

    /**
     * Canonize fields for display
     *
     * @param array $fields
     * @param array $meta
     *
     * @return array
     */
    protected function displayFields($fields, array $meta = [])
    {
        $result = [];
        if (!$meta) {
            $meta = Pi::registry('compound_field', 'user')->read($this->name);
            if (!$meta) {
                return $result;
            }
        }
        foreach ($fields as $item) {
            $record = [];
            foreach ($meta as $name => $field) {
                if (!isset($item[$name])) {
                    continue;
                }
                $record[$name] = [
                    'title' => $field['title'],
                    'value' => $item[$name],
                ];
            }
            $result[] = $record;
        }

        return $result;
    }
}
