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
use Pi\Acl\Acl as AclManager;

/**
 * Module list of different operation permission
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Moduleperm extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options)
    {
        $aclHandler = new AclManager('module-' . $options['type']);
        $modules = $aclHandler->setRole($options['role'])->getResources();

        return $modules;
    }

    /**
     * {@inheritDoc}
     * @param string $type Default as front:
     *          front - all active and allowed for front section;
     *          admin - all active and allowed for admin section;
     *          manage - all active and allowed for admin managed components.
     *
     * @param string|null $role
     */
    public function read($type = 'front', $role = null)
    {
        //$this->cache = false;
        if (null === $role) {
            $role = Pi::service('user')->getUser()->role();
        }
        $options = compact('type', 'role');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string $type Default as front:
     *          front - all active and allowed for front section;
     *          admin - all active and allowed for admin section;
     *          manage - all active and allowed for admin managed components.
     *
     * @param string|null $role
     */
    public function create($type = 'front', $role = null)
    {
        $this->clear('');
        $this->read($type, $role);

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
        return $this->clear('');
    }
}
