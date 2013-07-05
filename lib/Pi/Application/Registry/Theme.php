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

class Theme extends AbstractRegistry
{
    /**
     * Load installed themes, indexed by dirname, sorted by order
     *
     * @param array $options No use
     * @return array    keys: dirname, name, screenshot, author
     */
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('theme');
        $type = empty($options['type']) ? 'front' : $options['type'];

        $select = $model->select();
        $select->where->in('type', array('both', $type));
        $rowset = $model->selectWith($select);

        $themes = array();
        foreach ($rowset as $row) {
            $config = Pi::service('theme')->loadConfig($row->name);
            $themes[$row->name] = array(
                'title'         => $config['title'],
                'screenshot'    => !empty($config['screenshot']) ? Pi::service('asset')->getAssetUrl('theme/' . $row->name, $config['screenshot'], false) : Pi::url('static/image/theme.png'),
            );
        }
        /*
        if (!isset($themes['default'])) {
            $themes['default'] = array(
                'title'         => 'Default',
                'screenshot'    => Pi::service('asset')->getAssetUrl('theme/default', 'image/screenshot.png', false),
            );
        }
        */

        return $themes;
    }

    public function read($type = 'front')
    {
        $options = compact('type');
        return $this->loadData($options);
    }

    public function create()
    {
        $this->clear();
        $this->read();
        return true;
    }

    public function setNamespace($meta = null)
    {
        return parent::setNamespace('');
    }

    public function flush()
    {
        return $this->clear('');
    }
}
