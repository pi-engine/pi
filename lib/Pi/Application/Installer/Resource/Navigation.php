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
use Pi\Application\Model\Navigation\Node as NodeRow;
use Pi\Application\Model\Model as NavigationRow;

/**
 * Navigation setup with configuration specs
 *
 * NOTE:
 *
 * - Module front navigation won't be updated upon module upgrade
 * - Only top level items are shown in a non-system module admin menu
 *
 * ```
 *  array(
 *      'meta' => array(
 *          'name' => array( // Unique name
 *              'title'     => 'Title',
 *              'section'   => 'front',
 *          ),
 *          ...
 *      ),
 *      'item' => array(
 *          // front pages
 *          'front'    => array(
 *              // page with full module-controller-action and parameters
 *              'p1' => array(
 *                  'label'         => 'Front Page',
 *                  'controller'    => 'index',
 *                  'action'        => 'test',
 *                  'route'         => 'default',
 *                  'params'        => array(
 *                      'a'     => 'parama',
 *                      'b'     => 1
 *                  ),
 *                  // sub pages
 *                  'pages' => array(
 *                  ),
 *              ),
 *              'p2' => array(
 *                  'label'         => 'A Feed Page',
 *                  'controller'    => 'another',
 *                  'action'        => 'index',
 *                  'route'         => 'feed',
 *              ),
 *              'p3' => array(
 *                  'label'         => 'A Static Page',
 *                  // URI relative to Pi Engine www root
 *                  'uri'           => 'contact',
 *                  'resource'      => array(
 *                      'section'   => 'front',
 *                      'module'    => 'mvc',
 *                      'resource'  => 'test',
 *                      'item'      => 3,
 *                      'privilege' => 'read',
 *                  ),
 *              ),
 *              'p4' => array(
 *                  'label'         => 'A Static Page',
 *                  // URI relative to web www root
 *                  'uri'           => '/readme',
 *              ),
 *              // callback with array of class and method
 *              'p5' => array(
 *                  'callback'         => array('class', 'method'),
 *              ),
 *              // callback with single func
 *              'p6' => array(
 *                  'callback'         => 'Module\\System\\Navigation\\admin',
 *              ),
 *              // Divider with specified class
 *              'p7' => array(
 *                  'class'             => 'menu-divider',
 *              ),
 *              // Divider w/o specified class
 *              'p8' => array(),
 *              ...
 *          ),
 *      )
 *  );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Navigation extends AbstractResource
{
    /** @var string Current module identifier */
    protected $module;

    /**
     * Route for MVC pages as default
     * @var string
     */
    protected $route = 'default';

    /**
     * Canonize page data
     *
     * @param array $page
     * @return array
     */
    protected function canonizePage($page)
    {
        // @see: Zend\Navigation\Page\AbstractPage for identifying MVC pages
        $isMvc = !empty($page['action'])
            || !empty($page['controller'])
            || !empty($page['route']);
        if ($isMvc) {
            if (!isset($page['module'])) {
                $page['module'] = $this->module;
            }
            if (!isset($page['route'])) {
                $page['route'] = $this->route;
            }
            // Canonize module relative route
            if ('.' == $page['route'][0]) {
                $page['route'] = $page['module'] . '-'
                               . substr($page['route'], 1);
            }
            if (isset($page['params']) && !is_array($page['params'])) {
                $page['params'] = array();
            }
            //$validColumns = $this->mvcColumns;
        } else {
            if (empty($page['uri'])) {
                $page['uri'] = '';
            } elseif ('/' == $page['uri']) {
                $page['uri'] = Pi::url('www');
            } elseif (!preg_match('/^(http[s]?:\/\/|\/\/)/i', $page['uri'])) {
                $page['uri'] = Pi::url('www') . '/' . ltrim($page['uri'], '/');
            }
            //$validColumns = $this->uriColumns;
        }

        return $page;
    }

    /**
     * Canonize page list
     *
     * @param array $list
     * @return void
     */
    protected function canonizePages(&$list)
    {
        foreach ($list as $key => &$page) {
            $pages = array();
            if (!empty($page['pages'])) {
                $pages = $page['pages'];
            }
            $page = $this->canonizePage($page);
            if ($pages) {
                $this->canonizePages($pages);
                $page['pages'] = $pages;
            }
        }
    }

    /**
     * Normalize specs
     *
     * @param array $config
     * @return array
     */
    protected function canonizeConfig($config)
    {
        $module = $this->event->getParam('module');
        $this->module = $module;

        $result = array(
            'meta'  => array(),
            'node'  => array(),
        );

        $meta = array();
        if (!isset($config['item']) && !isset($config['meta'])) {
            $item = $config;
        } else {
            $meta = isset($config['meta']) ? $config['meta'] : array();
            $item =  isset($config['item']) ? $config['item'] : array();
        }

        // Set up front nav
        if (!isset($item['front'])) {
            $item['front'] = array();
        } elseif (false === $item['front']) {
            unset($item['front']);
        }

        foreach ($meta as $key => $nav) {
            $name           = $module . '-' . $key;
            $nav['module']  = $module;
            $nav['name']    = $name;
            $nav['title']   = __($nav['title']);
            $result['meta'][$name] = $nav;
        }
        foreach ($item as $key => $data) {
            $name = $module . '-' . $key;
            $this->canonizePages($data);
            $node = array(
                'module'        => $module,
                'navigation'    => $name,
                'data'          => $data,
            );
            $result['node'][$name] = $node;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->event->getParam('module');
        $message = array();

        $navigationList = $this->loadNavigation();
        // Insert navigation meta
        foreach ($navigationList['meta'] as $key => $navigation) {
            $status = $this->insertNavigation($navigation, $message);
            if (!$status) {
                $msg = 'Navigation "%s" is not created.';
                $message[] = sprintf($msg, $navigation['name']);
                return array(
                    'status'    => false,
                    'message'   => $message
                );
            }
        }

        // Insert navigation pages
        foreach ($navigationList['node'] as $key => $node) {
            $status = $this->insertNavigationNode($node, $message);
            if (!$status) {
                $msg = 'Navigation data for "%s" is not created.';
                $message[] = sprintf($msg, $node['navigation']);
                return array(
                    'status'    => false,
                    'message'   => $message
                );
            }
        }

        Pi::registry('navigation')->clear($module);
        Pi::service('cache')->clearByNamespace('nav');

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');

        if ($this->skipUpgrade()) {
            return;
        }
        $message = array();
        $navigationList = $this->loadNavigation();

        // Update navigation meta
        $navigations = $navigationList['meta'];
        $modelNavigation = Pi::model('navigation');
        $rowset = $modelNavigation->select(array('module' => $module));
        foreach ($rowset as $row) {
            // Updated existent navigation
            if (isset($navigations[$row->name])) {
                $status = $row->assign($navigations[$row->name])->save();
                unset($navigations[$row->name]);
                continue;
            // Delete deprecated navigation
            } else {
                $status = $this->deleteNavigation($row, $message);
                if (!$status) {
                    $msg = 'Deprecated navigation "%s" is not deleted.';
                    $message[] = sprintf($msg, $row->name);
                    return array(
                        'status'    => false,
                        'message'   => $message
                    );
                }
            }
        }
        // Add new navigations
        foreach ($navigations as $key => $navigation) {
            $status = $this->insertNavigation($navigation, $message);
            if (!$status) {
                $msg = 'Navigation "%s" is not created.';
                $message[] = sprintf($msg, $navigation['name']);
                return array(
                    'status'    => false,
                    'message'   => $message
                );
            }
        }

        // Update navigation nodes
        $nodes = $navigationList['node'];
        $modelNode = Pi::model('navigation_node');
        $rowset = $modelNode->select(array('module' => $module));
        foreach ($rowset as $row) {
            // Updated existent node
            if (isset($nodes[$row->navigation])) {
                $status = $row->assign($nodes[$row->navigation])->save();
                unset($nodes[$row->navigation]);
                continue;
            // Delete deprecated node
            } else {
                $row->delete();
            }
        }
        // Add new nodes
        foreach ($nodes as $key => $node) {
            $status = $this->insertNavigationNode($node, $message);
            if (!$status) {
                $msg = 'Navigation node "%s" is not created.';
                $message[] = sprintf($msg, $node['navigation']);
                return array(
                    'status'    => false,
                    'message'   => $message
                );
            }
        }

        Pi::registry('navigation')->clear($module);
        Pi::service('cache')->clearByNamespace('nav');

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');

        // Remove navigations
        $model = Pi::model('navigation');
        $rowset = $model->select(array('module' => $module));
        foreach ($rowset as $row) {
            $status = $this->deleteNavigation($row, $message);
            if (!$status) {
                $msg = 'Deprecated navigation "%s" is not deleted.';
                $message[] = sprintf($msg, $row->name);
                return array(
                    'status'    => false,
                    'message'   => $message
                );
            }
        }
        // Remove nodes
        $model = Pi::model('navigation_node');
        $rowset = $model->select(array('module' => $module));
        foreach ($rowset as $row) {
            $row->delete();
        }

        Pi::registry('navigation')->flush();
        Pi::service('cache')->clearByNamespace('nav');

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');

        // update role active => 1
        $where = array('module' => $module);
        Pi::model('navigation')->update(array('active' => 1), $where);
        Pi::registry('navigation')->flush();
        Pi::service('cache')->clearByNamespace('nav');

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module = $this->event->getParam('module');

        // update role active => 1
        $where = array('module' => $module);
        Pi::model('navigation')->update(array('active' => 0), $where);
        Pi::registry('navigation')->flush();
        Pi::service('cache')->clearByNamespace('nav');

        return true;
     }

    /**
     * Insert all pages of a navigation
     *
     * @param array $node
     * @param array $message
     * @return bool
     */
    protected function insertNavigationNode($node, &$message)
    {
        $row = Pi::model('navigation_node')->createRow($node);
        $row->save();

        return $row->id ? true : false;
    }

    /**
     * Delete a page node
     *
     * @param NodeRow $node
     * @param array $message
     * @return bool
     */
    protected function deleteNavigationNode(NodeRow $node, &$message = null)
    {
        $node->delete();

        return true;
    }

    /**
     * Load navigation specs from config
     *
     * @return array
     */
    protected function loadNavigation()
    {
        if (false === ($navigations = $this->config)) {
            return array();
        }
        $module = $this->event->getParam('module');
        Pi::service('i18n')->load(sprintf('module/%s:navigation', $module));
        $navigations = $this->canonizeConfig($navigations);

        return $navigations;
    }

    /**
     * Create a navigation
     *
     * @param array $navigation
     * @param array $message
     * @return bool
     */
    protected function insertNavigation($navigation, &$message)
    {
        $model = Pi::model('navigation');
        $row = $model->createRow($navigation);
        $row->save();
        if (!$row->id) {
            return false;
        }

        return $row->id;
    }

    /**
     * Delete a navigation
     *
     * @param NavigationRow $navigationRow
     * @param array $message
     * @return bool
     */
    protected function deleteNavigation(
        NavigationRow $navigationRow,
        &$message
    ) {
        try {
            $navigationRow->delete();
        } catch (\Exception $e) {
            $message[] = $e->getMessage();
            return false;
        }
        $row = Pi::model('navigation_node')->find(
            $navigationRow->name,
            'navigation'
        );
        if ($row) {
            $row->delete();
        }

        return true;
    }
}
