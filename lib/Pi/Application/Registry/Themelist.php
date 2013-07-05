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

class Themelist extends AbstractRegistry
{
    /**
     * Load raw data
     *
     * @param   array   $options potential values for type: front, admin, both
     * @return  array   keys: dirname, name, image, author, version
     */
    protected function loadDynamic($options)
    {
        $model = Pi::model('theme');
        $type = empty($options['type']) ? 'front' : $options['type'];

        $select = $model->select();
        $select->where->in('type', array('both', $type));
        $rowset = $model->selectWith($select);

        $themes = array();
        foreach ($rowset as $row) {
            $config = Pi::service('theme')->loadConfig($row->name);
            $config['screenshot'] = !empty($config['screenshot']) ? Pi::service('asset')->getAssetUrl('theme/' . $row->name, $config['screenshot'], false) : Pi::url('static/image/theme.png');
            $themes[$row->name] = array_merge($config, $row->toArray());
        }

        return $themes;
    }

    public function read($type = null)
    {
        $options = compact('type');
        return $this->loadData($options);
    }

    public function create($type = '')
    {
        $this->clear();
        $this->read($type);
        return true;
    }

    public function setNamespace($meta)
    {
        return parent::setNamespace('');
    }

    public function flush()
    {
        return $this->clear('');
    }
}
