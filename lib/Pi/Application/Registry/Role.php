<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * Role list
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Role extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('acl_role');
        $ancestors = $model->getAncestors($options['role']);

        return $ancestors;
    }

    /**
     * {@inheritDoc}
     * @param string $role
     */
    public function read($role = '')
    {
        //$this->cache = false;
        $options = compact('role');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string $role
     */
    public function create($role = '')
    {
        $this->clear($role);
        $this->read($role);

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
    public function clear($namespace = '')
    {
        parent::clear('');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->clear('');

        return $this;
    }
}
