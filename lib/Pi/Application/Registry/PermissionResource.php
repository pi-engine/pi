<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * Permission resource list
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PermissionResource extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $result = array();
        if ('page' == $options['type']) {
            $model = Pi::model('page');
            $where = array(
                'section'   => $options['section'],
                'module'    => $options['module'],
            );
            $rowset = $model->select($where);
            foreach ($rowset as $row) {
                if (!$row['permission']) {
                    continue;
                }
                $key = $row['module'];
                if ($row['controller']) {
                    $key .= '-' . $row['controller'];
                    if ($row['action']) {
                        $key .= '-' . $row['action'];
                    }
                }
                $result[$key] = $row['permission'];
            }
        } else {
            $model = Pi::model('permission_resource');
            $where = array(
                'section'   => $options['section'],
                'module'    => $options['module'],
                'type'      => $options['type'],
            );
            $rowset = $model->select($where);
            foreach ($rowset as $row) {
                $result[$row->name] = $row->toArray();
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * Get all resources with specific section, module and type
     *
     * @param string        $section   Section name: front, admin, module
     * @param string        $module    Module name
     * @param string|null   $type      system, callback or page
     */
    public function read($section = 'front', $module = '', $type = null)
    {
        //$this->cache = false;
        $module = $module ?: Pi::service('module')->current();
        $type = $type ?: 'system';
        $options = compact('section', 'module', 'type');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string        $section   Section name: front, admin, module
     * @param string        $module    Module name
     * @param string|null   $type      system, page or other custom types
     */
    public function create($section = 'front', $module = '', $type = null)
    {
        $module = $module ?: Pi::service('module')->current();
        $this->clear($module);
        $this->read($module, $section, $type);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->clear('');
        $this->flushByModules();

        return $this;
    }
}
