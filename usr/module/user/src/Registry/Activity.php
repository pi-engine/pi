<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\User\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Pi user activity registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Activity extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = [])
    {
        $list = [];

        $model  = Pi::model('activity', $this->module);
        $where  = ['active' => 1, 'display > 0'];
        $select = $model->select()->where($where)->order('display');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $list[$row['name']] = [
                'title'       => $row['title'],
                'description' => $row['description'],
                'module'      => $row['module'],
                'icon'        => $row['icon'],
                'callback'    => $row['callback'],
                'template'    => $row['template'],
            ];
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
        $options = [];
        $data    = $this->loadData($options);
        if ($name) {
            $result = isset($data[$name]) ? $data[$name] : [];
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
