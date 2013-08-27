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
 * Pi user timeline registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timeline extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $model = Pi::model('timeline', $this->module);
        $rowset = $model->select(array('active' => 1));
        foreach ($rowset as $row) {
            $list[$row['name']] =  array(
                'title'         => $row['title'],
                'module'        => $row['module'],
                'icon'          => $row['icon'],
            );
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     * @param string $name Activity name
     * @param array
     */
    public function read($name = '')
    {
        $options = array();
        $data = $this->loadData($options);
        if ($name) {
            $result = isset($data[$name]) ? $data[$name] : array();
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     */
    public function create($name = '')
    {
        $this->clear('');
        $this->read($name);

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
