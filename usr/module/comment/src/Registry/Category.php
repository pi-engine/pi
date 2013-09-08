<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Module\Comment\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Pi comment category registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Category extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'comment';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $model = Pi::model('category', $this->module);
        $rowset = $model->select(array('active' => 1));
        foreach ($rowset as $row) {
            $list[$row['module']][$row['name']] =  array(
                'title'     => $row['title'],
                'callback'  => $row['callback'],
                'icon'      => $row['icon'],
            );
        }

        return $list;
    }

    /**
     * {@inheritDoc}
     * @param string $module
     * @param string $category
     * @param array
     */
    public function read($module = '', $category = '')
    {
        $options = array();
        $data = $this->loadData($options);
        if ($module) {
            $data = isset($data[$module]) ? $data[$module] : array();
            if ($category) {
                $data = isset($data[$category]) ? $data[$category] : array();
            }
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     */
    public function create($meta = '')
    {
        $this->clear('');
        $this->read('');

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
