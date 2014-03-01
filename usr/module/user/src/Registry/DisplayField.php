<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Module\User\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Profile display group fields
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DisplayField extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $where = array();
        if (!empty($options['group'])) {
            $where = array('group' => $options['group']);
        }
        $model  = Pi::model('display_field', $this->module);
        $select = $model->select()->where($where);
        $select->order('order ASC');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $list[] = $row['field'];
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     * @param array
     */
    public function read($group = 0)
    {
        $options = compact('group');
        $result = $this->loadData($options);

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param bool $name
     */
    public function create()
    {
        $this->clear('');
        $this->read();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        return parent::setNamespace('');
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        return $this->clear('');
    }
}
