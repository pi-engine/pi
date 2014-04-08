<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Module\Comment\Registry;

use Pi;
use Pi\Application\Registry\AbstractRegistry;

/**
 * Pi comment type registry
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Type extends AbstractRegistry
{
    /** @var string Module name */
    protected $module = 'comment';

    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $list = array();

        $model = Pi::model('type', $this->module);
        if (-1 != $options['active']) {
            $where = array('active' => $options['active']);
        } else {
            $where = array();
        }
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
                'active'        => $row['active'],
                'callback'      => $row['callback'],
                'locator'       => $row['locator'],
                'identifier'    => $row['identifier'],
                'params'        => $row['params'],
            );
            $name       = $row['name'];
            $controller = $row['controller'];
            $action     = $row['action'];

            if ($options['module']) {
                if ($options['type']) {
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
     * @param string    $module
     * @param string    $type
     * @param bool|null $active
     */
    public function read($module = '', $type = '', $active = true)
    {
        $catName    = $type;
        $type       = $catName ? 1 : 0;
        $active     = (null === $active) ? -1 : (int) $active;
        $options    = compact('module', 'type', 'active');
        $data       = $this->loadData($options);
        if ($module && $catName) {
            $data = isset($data[$catName]) ? $data[$catName] : array();
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
