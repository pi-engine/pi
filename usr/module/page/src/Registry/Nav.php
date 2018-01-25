<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\Page\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Page navigation menu
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Nav extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'page';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = [])
    {
        $list = [];

        $model  = Pi::model('page', $this->module);
        $select = $model->select();
        $select->where(['active' => 1, 'nav_order > ?' => 0]);
        $select->order('nav_order ASC');
        $select->columns(['id', 'title', 'name', 'slug']);
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $id        = (int)$row['id'];
            $name      = $row['slug'] ?: $row['name'];
            $url       = Pi::service('url')->assemble('page', [
                'module' => $this->module,
                'name'   => $name,
            ]);
            $item      = [
                'id'    => $id,
                'title' => $row['title'],
                'url'   => $url,
            ];
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
        $options = [];
        $result  = $this->loadData($options);

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
