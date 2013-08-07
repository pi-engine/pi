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
 * ACL resource list
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Resource extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $ancestors = array();
        $model = Pi::model('acl_resource')->setSection($options['section']);
        $where = array('section' => $options['section']);
        $where['module'] = $options['module'];
        if (null !== $options['type']) {
            $where['type'] = $options['type'];
        }
        $rowset = $model->select($where);
        if (!$rowset->count()) {
            return $ancestors;
        }
        foreach ($rowset as $row) {
            $ancestors[$row->name] = $model->getAncestors($row, 'id');
            /*
            if (!empty($options['self'])) {
                $ancestors[$row->name][] = $row->id;
            }
            */
        }

        return $ancestors;
    }

    /**
     * {@inheritDoc}
     *
     * Get all resources with specific section, module and type
     *
     * @param string        $section   Section name: front, admin, module
     * @param string        $module    Module name
     * @param string|null   $type      system, page or other custom types
     */
    public function read($section = 'front', $module = '', $type = null)
    {
        //$this->cache = false;
        $module = $module ?: Pi::service('module')->current();
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
