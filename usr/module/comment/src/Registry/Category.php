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
        $where = array('active' => 1);
        if ($options['module']) {
            $where['module'] = $options['module'];
        }
        $rowset = $model->select($where);
        foreach ($rowset as $row) {
            $data = array(
                'title'         => $row['title'],
                'identifier'    => $row['identifier'],
                //'category'      => $row['name'],
                'callback'      => $row['callback'],
                'icon'          => $row['icon'],
                //'module'        => $row['module'],
                //'controller'    => $row['controller'],
                //'action'        => $row['action'],
                'params'        => $row['params'],
            );
            if ($options['module']) {
                if ($options['category']) {
                    $data = array_merge($data, array(
                        'controller'    => $row['controller'],
                        'action'        => $row['action'],
                    ));
                    $list[$row['name']] = $data;
                } else {
                    $list[][$row['action']][$row['name']] = $data;
                }
            } else {
                $data = array_merge($data, array(
                    'controller'    => $row['controller'],
                    'action'        => $row['action'],
                ));
                $list[$row['module']][$row['name']] = $data;
            }
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
        $options = array('module' => $module, 'category' => $category ? 1 : 0);
        $data = $this->loadData($options);
        if ($module && $category) {
            $data = isset($data[$category]) ? $data[$category] : array();
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
        $this->read($meta);

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
