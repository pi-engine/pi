<?php
/**
 * Pi cache registry
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Application
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Pi\Application\Registry;
use Pi;

class Route extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('route');
        $select = $model->select()->columns(array('name', 'data'))->order('priority ASC, id ASC');
        if (empty($options['exclude'])) {
            $select->where
                ->equalTo('active', 1)
                ->NEST
                    ->equalTo('section', $options['section'])
                    ->OR
                    ->equalTo('section', '')
                ->UNNEST;
        } else {
            $select->where(array(
                'active'        => 1,
                'section <> ?'  => $options['section'],
            ));
        }
        $rowset = $model->selectWith($select);

        $configs = array();
        foreach ($rowset as $row) {
            $configs[$row->name] = $row->data;
        }

        return $configs;
    }

    public function read($section, $exclude = false)
    {
        $options = compact('section', 'exclude');
        $data = $this->loadData($options);
        return $data;
    }

    public function create($section, $exclude = false)
    {
        $this->clear($section);
        $this->read($section, $exclude);
        return true;
    }

    public function setNamespace($meta)
    {
        if (is_string($meta)) {
            $namespace = $meta;
        } else {
            $namespace = $meta['section'];
        }
        return parent::setNamespace($namespace);
    }

    public function flush()
    {
        $this->flushBySections();
        return $this;
    }
}
