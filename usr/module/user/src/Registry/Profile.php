<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Module\User\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Pi user meta registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profile extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $fields = array();
        return $fields;
    }

    /**
     * {@inheritDoc}
     * @param string $type Field types: account, profile, custom
     * @param string $action Actions: display, edit, search
     * @param array
     */
    public function read($type = '', $action = '')
    {
        $options = compact('type', 'action');
        $data = $this->loadData($options);

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param string $action
     */
    public function create($action = '')
    {
        $this->clear('');
        $this->read($action);

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
