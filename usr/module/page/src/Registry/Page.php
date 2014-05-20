<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\Page\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Page list
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Page extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'page';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $model  = Pi::model('page', $this->module);
        $select = $model->select();
        $select->where(array('active' => 1));
        $select->columns(array('id', 'name', 'slug'));
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $id = (int) $row['id'];
            $item = array(
                'name'  => $row['name'],
                'slug'  => $row['slug'],
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
