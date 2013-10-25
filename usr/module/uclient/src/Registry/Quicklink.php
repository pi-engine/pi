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
 * Pi user quicklink registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Quicklink extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $model = Pi::model('quicklink', $this->module);
        $where = array('active' => 1, 'display > 0');
        $select = $model->select()->where($where)->order('display');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $list[$row['name']] =  array(
                'title'         => $row['title'],
                'module'        => $row['module'],
                'icon'          => $row['icon'],
                'link'          => $row['link'],
            );
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        $options = array();
        $result = $this->loadData($options);

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
