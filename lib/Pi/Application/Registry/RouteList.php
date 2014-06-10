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
 * Route List
 *
 * Note:
 * Route names for cloned modules are indexed by a string composed of module
 * name and route name
 *
 * @see     Pi\Mvc\Router\Http\TreeRouteStack
 * @author  Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RouteList extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $where = array('module' => $options['module']);
        if (!empty($options['section'])) {
            $where['section'] = $options['section'];
        }
        $model  = Pi::model('route');
        $rowset = $model->select($where);

        $configs = array();
        foreach ($rowset as $row) {
            $spec = $row->data;
            if ($row->priority) {
                $spec['priority'] = $row->priority;
            }
            /*
            $directory = Pi::service('module')->directory($row->module);
            if ($directory && $row->module != $directory) {
                $name = $row->module . '-' . $row->name;
            } else {
                $name = $row->name;
            }
            */
            $name = $row->name;
            $configs[$name] = $spec;
        }

        return $configs;
    }

    /**
     * {@inheritDoc}
     * @param string    $module
     * @param string    $section
     */
    public function read($module = '', $section = '')
    {
        $options = compact('module', 'section');
        $data = $this->loadData($options);

        return $data;
    }

    /**
     * {@inheritDoc}
     * @param string    $module
     * @param string    $section
     */
    public function create($module = '', $section = '')
    {
        $this->clear($module);
        $this->read($module);

        return true;
    }
}
