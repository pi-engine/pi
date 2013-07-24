<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * Route setup
 *
 * For a non-system module route, route name is prefixed with module name:
 *  "<route-name>" transiliated to "<module-name>-<route-name>"
 *
 *
 * ```
 * array(
 *  // A specified route
 *  'name'   => array(
 *      // section: front, admin, feed, etc.
 *      'section'   => 'front',
 *      // order to call
 *      'priority'  => -999,
 *      // Type defined in \Pi\Mvc\Router\Route
 *      'type'      => 'Standard',
 *      'options'   =>array(
 *          'route' => '', // Used as prefix, which is different from Zend routes
 *          'structure_delimiter'   => '/',
 *          'param_delimiter'       => '/',
 *          'key_value_delimiter'   => '-',
 *          'defaults'              => array(
 *              'module'        => 'system',
 *              'controller'    => 'public',
 *              'action'        => 'index',
 *          )
 *      )
 *  ),
 * );
 * ```
 *
 * @see \Pi\Mvc\Router\Http\TreeRouteStack::canonizeRoute() for route name utilization
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Route extends AbstractResource
{
    /**
     * Canonizes route config
     *
     * For a non-system module route, route name is prefixed with module name:
     *  "<route-name>" transiliated to "<module-name>-<route-name>"
     *
     * @param array $configs
     * @return array
     */
    protected function canonize(array $configs)
    {
        $module = $this->event->getParam('module');

        $routes = array();
        foreach ($configs as $name => $data) {
            if ('system' != $module) {
                $name = $module . '-' . $name;
                $data['module'] = $module;
            }
            $route = array(
                'name'      => $name,
                'section'   => 'front',
                'module'    => $module,
                'priority'  => 0,
            );
            if (isset($data['priority'])) {
                $route['priority'] = $data['priority'];
                unset($data['priority']);
            }
            if (isset($data['section'])) {
                $route['section'] = $data['section'];
                unset($data['section']);
            }
            $route['data'] = $data;
            $routes[$name] = $route;
        }

        return $routes;
    }

    /**
     * Canonize route specifications
     *
     * @param array $data
     * @return array
     */
    protected function canonizeRoute(array $data)
    {
        $module = $this->event->getParam('module');

        $route = array(
            'section'   => 'front',
            'module'    => $module,
            'priority'  => 0,
        );
        if (isset($data['priority'])) {
            $route['priority'] = $data['priority'];
            unset($data['priority']);
        }
        if (isset($data['section'])) {
            $route['section'] = $data['section'];
            unset($data['section']);
        }
        $route['data'] = $data;

        return $route;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }
        //$module = $this->event->getParam('module');

        $modelRoute = Pi::model('route');
        $routes = $this->canonize($this->config);

        foreach ($routes as $name => $data) {
            //$data = $this->canonizeRoute($route);
            //$data['name'] = $name;
            $row = $modelRoute->createRow($data);
            $status = $row->save();
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf('Route "%s" is not created.', $name)
                );
            }
        }
        Pi::service('registry')->route->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->route->flush();
        if ($this->skipUpgrade()) {
            return;
        }

        $modelRoute = Pi::model('route');
        $modelRoute->delete(array('module' => $module, 'custom' => 0));
        $routes = $this->canonize($this->config);

        foreach ($routes as $name => $data) {
            $row = $modelRoute->createRow($data);
            $status = $row->save();
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf('Route "%s" is not created.', $name)
                );
            }
        }
        Pi::service('registry')->route->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        $modelRoute = Pi::model('route');
        $modelRoute->delete(array('module' => $module));
        Pi::service('registry')->route->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');
        $modelRoute = Pi::model('route');
        $modelRoute->update(array('active' => 1), array('module' => $module));
        Pi::service('registry')->route->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        $modelRoute = Pi::model('route');
        $modelRoute->update(array('active' => 0), array('module' => $module));
        Pi::service('registry')->route->flush();

        return true;
    }
}
