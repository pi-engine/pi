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
    protected function loadDynamic($options = [])
    {
        $list = [];

        $model  = Pi::model('timeline', $this->module);
        $rowset = $model->select(['active' => 1]);
        foreach ($rowset as $row) {
            $list[$row['name']] = [
                'title'  => $row['title'],
                'module' => $row['module'],
                'icon'   => $row['icon'],
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
