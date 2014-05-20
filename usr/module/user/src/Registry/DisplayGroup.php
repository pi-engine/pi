<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\User\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Profile display group
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DisplayGroup extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $model  = Pi::model('display_group', $this->module);
        $select = $model->select()->order('order ASC');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $list[$row['id']] =  $row->toArray();
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     * @param array
     */
    public function read($group = 0)
    {
        $options = array();
        $result = $this->loadData($options);
        if ($group) {
            $result = isset($result[$group]) ? $result[$group] : array();
        }

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
