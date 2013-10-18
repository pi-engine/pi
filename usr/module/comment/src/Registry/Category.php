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
        } else {
            $moduleList = Pi::registry('modulelist')->read('active');
            $where['module'] = array_keys($moduleList);
        }
        $rowset = $model->select($where);
        foreach ($rowset as $row) {
            $data = array(
                'title'         => $row['title'],
                'icon'          => $row['icon'],
                //'category'      => $row['name'],
                'callback'      => $row['callback'],
                'locator'       => $row['locator'],
                //'module'        => $row['module'],
                //'controller'    => $row['controller'],
                //'action'        => $row['action'],
                'identifier'    => $row['identifier'],
                'params'        => $row['params'],
            );
            $name       = $row['name'];
            $controller = $row['controller'];
            $action     = $row['action'];

            if ($options['module']) {
                if ($options['category']) {
                    if (empty($row['locator'])) {
                        $data = array_merge($data, array(
                            'controller'    => $controller,
                            'action'        => $action,
                        ));
                    }
                    $list[$row['name']] = $data;
                } else {
                    if (empty($row['locator'])) {
                        $list['route'][$controller][$action][$name] = $data;
                    } else {
                        $list['locator'][$name] = $data;
                    }
                }
            } else {
                if (empty($row['locator'])) {
                    $data = array_merge($data, array(
                        'controller'    => $controller,
                        'action'        => $action,
                    ));
                }
                $list[$row['module']][$name] = $data;
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
        //vd($data);
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
