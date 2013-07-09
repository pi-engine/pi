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

class Role extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $model = Pi::model("acl_role");
        $ancestors = $model->getAncestors($options['role']);

        return $ancestors;
    }

    public function read($role)
    {
        //$this->cache = false;
        $options = compact('role');
        return $this->loadData($options);
    }

    public function create($role)
    {
        $this->clear($role);
        $this->read($role);
        return true;
    }

    public function setNamespace($meta)
    {
        return parent::setNamespace('');
    }

    public function clear($namespace = '')
    {
        parent::clear('');
        return $this;
    }

    public function flush()
    {
        $this->clear('');
        return $this;
    }
}
