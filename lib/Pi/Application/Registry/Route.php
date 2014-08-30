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
 * Routes
 *
 * Note:
 * Route names for cloned modules are indexed by a string composed of module
 * name and route name
 *
 * @see     Pi\Mvc\Router\Http\TreeRouteStack
 * @author  Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Route extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $model  = Pi::model('route');
        $select = $model->select();
        if (empty($options['exclude'])) {
            $select->where
                ->equalTo('active', 1)
                ->NEST
                    ->equalTo('section', $options['section'])
                    ->OR
                    ->equalTo('section', '')
                ->UNNEST;
        } else {
            $select->where(array(
                'active'        => 1,
                'section <> ?'  => $options['section'],
            ));
        }
        $rowset = $model->selectWith($select);

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
     * @param string    $section
     * @param bool      $exclude    To exclude the specified section
     */
    public function read($section = 'front', $exclude = false)
    {
        $options = compact('section', 'exclude');
        $data = $this->loadData($options);

        return $data;
    }

    /**
     * {@inheritDoc}
     * @param string    $section
     * @param bool      $exclude    To exclude the specified section
     */
    public function create($section = 'front', $exclude = false)
    {
        $this->clear($section);
        $this->read($section, $exclude);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($meta = '')
    {
        if (is_string($meta)) {
            $namespace = $meta;
        } else {
            $namespace = $meta['section'];
        }

        return parent::setNamespace($namespace);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->flushBySections();

        return $this;
    }
}
