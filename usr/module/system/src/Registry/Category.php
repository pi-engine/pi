<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\System\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Module category
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Category extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'system';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $model  = Pi::model('category', $this->module);
        $select = $model->select()->order('order ASC');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $id = (int) $row['id'];
            $item = array(
                'id'        => $id,
                'title'     => $row['title'],
                'icon'      => $row['icon'],
                'modules'   => $row['modules'],
            );
            $list[$id] = $item;
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     * @param array
     */
    public function read()
    {
        $options = array();
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
