<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        $result = array();
        if ($this->isMultiple) {
            $select = $this->getModel()->select();
            $select->order('order ASC');
            $select->where(array('uid' => $uid));
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[] = $row->toArray();
            }
        } else {
            $row = $this->getModel()->find($uid, 'uid');
            $result = $row ? $row->toArray() : array();
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
                $result[(int) $row['uid']][] = $row->toArray();
            }
        } else {
            $rowset = $this->getModel()->selectWith($select);
            foreach ($rowset as $row) {
                $result[(int) $row['uid']] = $row->toArray();
            }
        }

        return $result;
    }
}
