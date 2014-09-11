<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Field;

use Pi;

/**
 * Abstract class for custom compound handling
 *
 * {@inheritDoc}
 * @author Zongshu Lin <lin40553024@163.com>
 */
abstract class CustomCompoundHandler extends AbstractCustomHandler
{
    /**
     * {@inheritDoc}
     */
    public function get($id, $filter = false)
    {
        $result = array();
        if ($this->isMultiple) {
            $select = $this->getModel()->select();
            $select->order('order ASC');
            $select->where(array('article' => $id));
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[] = $row->toArray();
            }
        } else {
            $row = $this->getModel()->find($id, 'article');
            $result[] = $row ? $row->toArray() : array();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function mget($ids, $filter = false)
    {
        $result = array();
        $select = $this->getModel()->select();
        $select->where(array('article' => $ids));
        if ($this->isMultiple) {
            $select->order('order ASC');
        }
        $rowset = $this->getModel()->selectWith($select);
        foreach ($rowset as $row) {
            $result[(int) $row['article']][] = $row->toArray();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function display($id, $data = null)
    {
        $result = array();

        $meta = Pi::registry('compound_field', $this->module)->read($this->name);
        if (!$meta) {
            return $result;
        }

        if (is_scalar($id)) {
            $ids = (array) $id;
            if (null !== $data) {
                $data = array($id => $data);
            }
        } else {
            $ids = $id;
        }
        if (null === $data) {
            $data = $this->mget($ids, true);
        }

        array_walk($data, function (&$list) use ($meta) {
            $list = $this->displayFields($list, $meta);
        });

        if (is_scalar($id)) {
            $data = isset($data[$id]) ? $data[$id] : array();
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
    protected function displayFields($fields, array $meta = array())
    {
        $result = array();
        if (!$meta) {
            $meta = Pi::registry('compound_field', $this->module)->read($this->name);
            if (!$meta) {
                return $result;
            }
        }
        foreach ($fields as $item) {
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
            $result[] = $record;
        }

        return $result;
    }
}
