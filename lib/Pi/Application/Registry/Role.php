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
        $result = array();
        $model = Pi::model('role');
        $rowset = $model->select(array('active' => 1));
        foreach ($rowset as $row) {
            $result[$row['section']][$row['name']] = $row['title'];
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param string $section
     */
    public function read($section = '')
    {
        $options = array();
        $data = $this->loadData($options);
        if ($section) {
            $data = $data[$section];
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     * @param string $section
     */
    public function create($section = '')
    {
        $this->clear($section);
        $this->read($section);

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
