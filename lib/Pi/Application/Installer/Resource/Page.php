<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * Page setup with configuration specs
 *
 * ```
 *  array(
 *          // font mvc pages
 *          'front' => array(
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'cache_ttl'     => 0,
 *                  'cache_level'   => '',
 *                  // Block inheritance:
 *                  // 1 - specified; 0 - inherit from parent
 *                  'block'         => 0,
 *              ),
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *                  'cache_ttl'     => 60,
 *                  'cache_level'   => 'role',
 *                  // Block inheritance:
 *                  // 1 - specified; 0 - inherit from parent
 *                  'block'         => 1,
 *              ),
 *              ...
 *          ),
 *          // admin mvc pages
 *          'admin'  => array(
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *                  'cache_ttl'  => 0,
 *                  'cache_level'   => '',
 *                  // Permission name for access
 *                  'permission'    => <permission-name>,
 *              ),
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *                  'cache_ttl'  => 0,
 *                  'cache_level'   => '',
 *              ),
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *                  'cache_ttl'  => 0,
 *                  'cache_level'   => '',
 *              ),
 *              ...
 *          ),
 *          // feed mvc pages
 *          'feed'  => array(
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *                  'cache_ttl'  => 0,
 *                  'cache_level'   => '',
 *              ),
 *              ...
 *          ),
 *          // Exception of admin pages to skip ACL check
 *          'exception' => array(
 *              array(
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *              ),
 *              ...
 *          ),
 *          ...
 *  );
 * ```
 *
 * Disable pages
 *
 *  ```
 *  return false;
 *  ```
 *
 * Disable a section
 *
 *  ```
 *  return array(
 *      'front' => false,
 *      'admin' => array(
 *      ),
 *  );
 *  ```
 *
 * @link Pi\Acl\Acl\Resource
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Page extends AbstractResource
{
    /**
     * Canonize config specs
     *
     * @param array $config
     * @return array
     */
    protected function canonize($config)
    {
        //$moduleTitle = $this->event->getParam('title');
        $pageEntry = array(
            'title' => 'Module wide',
            'block' => 1,
        );
        /*
        // Set module exception for admin
        if (empty($config['admin']) && !isset($config['exception'])) {
            $config['exception'] = array($pageEntry);
        }
        */
        if (!isset($config['front']) || false !== $config['front']) {
            if (!isset($config['front'])) {
                $config['front'] = array();
            }
            $config['front'][] = $pageEntry;
        }
        if (!isset($config['admin']) || false !== $config['admin']) {
            if (!isset($config['admin'])) {
                $config['admin'] = array();
            }
            $config['admin'][] = $pageEntry;
        }
        foreach ($config as $section => &$list) {
            if (false === $list) {
                continue;
            }
            foreach ($list as $index => &$page) {
                $page['section'] = $section;
                $page = $this->canonizePage($page);
            }
        }
        /*
        if (!empty($config['front'])) {
            foreach ($config['front'] as $index => &$page) {
                $page['section'] = 'front';
                $page = $this->canonizePage($page);
            }
        }
        if (!empty($config['admin'])) {
            foreach ($config['admin'] as $index => &$page) {
                $page['section'] = 'admin';
                $page = $this->canonizePage($page);
            }
        }
        */

        return $config;
    }
    /**
     * Canonize page specs
     *
     * @param array $page
     * @return array
     */
    protected function canonizePage(array $page)
    {
        $columnsPage = array(
            'title',
            'section', 'module', 'controller', 'action', 'permission',
            'cache_ttl', 'cache_level', 'block', 'custom'
        );

        $data = array();
        foreach ($page as $col => $val) {
            if (in_array($col, $columnsPage)) {
                $data[$col] = $val;
            }
        }
        if (empty($data['module'])) {
            $data['module'] = $this->getModule();
        }
        /*
        if (!empty($data['permission'])) {
            $data['permission'] = $page['module'] . '-' . $data['permission'];
        }
        */

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        // Skip if pages disabled
        if (false === $this->config) {
            return;
        }
        $module = $this->event->getParam('module');
        //$moduleTitle = $this->event->getParam('title');
        Pi::registry('page')->clear($module);
        $pages = $this->canonize($this->config);

        foreach (array_keys($pages) as $section) {
            // Skip the section if disabled
            if ($pages[$section] === false) continue;
            $pageList = array();
            foreach ($pages[$section] as $key => $page) {
                //$page['section'] = $section;
                //$page['module'] = $module;
                $pageName = $page['module'];
                if (!empty($page['controller'])) {
                    $pageName .= '-' . $page['controller'];
                    if (!empty($page['action'])) {
                        $pageName .= '-' . $page['action'];
                    }
                }
                if (empty($page['title'])) {
                    $page['title'] = $pageName;
                }
                $pageList[$pageName] = $page;
            }
            // Sort page list by module-controller-action
            ksort($pageList);
            $pages[$section] = $pageList;
        };

        /*
        // Set module access for front
        if (!empty($pages['front'])
            && !isset($pages['front'][$module]['permission']['access'])) {
            $pages['front'][$module]['permission']['access'] = array(
                'member'    => 1,
                'guest'     => 1
            );
        }
        // Set module access for admin
        if (!empty($pages['admin'])
            && !isset($pages['admin'][$module]['permission']['access'])) {
            $pages['admin'][$module]['permission']['access'] = array(
                'staff'     => 1
            );
        }
        */

        foreach ($pages as $section => $pageList) {
            foreach ($pageList as $name => $page) {
                $message = array();
                $status = $this->insertPage($page, $message);
                if (false === $status) {
                    $msg = 'Page "%s" is not created.';
                    $message[] = sprintf($msg, $page['title']);
                    return array(
                        'status'    => false,
                        'message'   => $message
                    );
                }
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('page')->clear($module);
        if ($this->skipUpgrade()) {
            return;
        }

        if ($this->config === false) {
            $pages = array();
            $disablePage = true;
        } else {
            $pages = $this->canonize($this->config);
            $disablePage = false;
        }

        $model = Pi::model('page');
        $rowset = $model->select(array('module' => $module));
        $pages_exist = array();
        foreach ($rowset as $page) {
            $key = sprintf(
                '%s:%s:%s:%s',
                $page['section'],
                $page['module'],
                $page['controller'],
                $page['action']
            );
            $pages_exist[$key] = $page;
        }

        foreach ($pages as $section => $pageList) {
            foreach ($pageList as $page) {
                //$page['section'] = $section;
                //$page['module'] = $module;
                if (empty($page['title'])) {
                    $pageName = $page['module'];
                    if (!empty($page['controller'])) {
                        $pageName .= '-' . $page['controller'];
                        if (!empty($page['action'])) {
                            $pageName .= '-' . $page['action'];
                        }
                    }
                    $page['title'] = $pageName;
                }
                $controller = isset($page['controller'])
                    ? $page['controller'] : '';
                $action = isset($page['action']) ? $page['action'] : '';
                $key = sprintf(
                    '%s:%s:%s:%s',
                    $section,
                    $page['module'],
                    $controller,
                    $action
                );
                //echo ' [' . $key . '] ';
                if (isset($pages_exist[$key])) {
                    $page_exist = $pages_exist[$key];
                    $data = array();
                    if ($page_exist['custom']) {
                        $data['custom'] = 0;
                    }
                    if ($page['title'] != $page_exist['title']) {
                        $data['title'] = $page['title'];
                    }
                    if (!empty($data)) {
                        $status = $model->update(
                            $data,
                            array('id' => $page_exist['id'])
                        );
                        if (!$status) {
                            $msg = 'Page "%s" is not updated.';
                            return array(
                                'status'    => false,
                                'message'   => sprintf($msg, $page['title'])
                            );
                        }
                    }
                    unset($pages_exist[$key]);
                    continue;
                }

                $message = array();
                $status = $this->insertPage($page, $message);
                if (!$status) {
                    $message[] = sprintf(
                        'Page "%s" is not created.',
                        $page['title']
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message
                    );
                }
            }
        }

        foreach ($pages_exist as $key => $page) {
            if ($page['custom'] && !$disablePage) continue;
            $message = array();
            $status = $this->deletePage($page, $message);
            if (false === $status) {
                $message[] = sprintf(
                    'Deprecated page "%s" is not deleted.',
                    $page['title']
                );
                return array(
                    'status'    => false,
                    'message'   => $message
                );
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('page')->clear($module);

        $model = Pi::model('page');
        $rowset = $model->select(array('module' => $module));
        foreach ($rowset as $row) {
            $message = array();
            $this->deletePage($row, $message);
        }

        return;
    }

    /**
     * Insert a page
     *
     * @param array $page
     * @param array $message
     * @return bool
     */
    protected function insertPage($page, &$message)
    {
        $modelPage = Pi::model('page');
        /*
        //$modelResource = Pi::model('permission_resource');
        $columnsPage = array(
            'title',
            'section', 'module', 'controller', 'action', 'permission',
            'cache_ttl', 'cache_level', 'block', 'custom'
        );

        $data = array();
        foreach ($page as $col => $val) {
            if (in_array($col, $columnsPage)) {
                $data[$col] = $val;
            }
        }
        */
        $message = array();
        // Insert page
        $pageRow = $modelPage->createRow($page);
        $pageRow->save();
        if (!$pageRow->id) {
            $message[] = sprintf('Page "%s" is not saved.', $page['title']);
            return false;
        }

        /*
        // Set up permission resource

        // If no permission is specified, skip
        if (empty($page['permission']) || empty($page['controller'])) {
            return true;
        }

        $resource = array();
        foreach ($page as $col => $val) {
            if (in_array($col, $columnsResource)) {
                $resource[$col] = $val;
            }
        }
        $resource['id'] = null;
        $resource['name'] = $page['module'] . '-' . $page['controller'];
        if (!empty($page['action'])) {
            $resource['name'] .= '-' . $page['action'];
        }
        $resource['type'] = 'page';
        $row = $modelResource->createRow($resource);
        $resourceId = $row->save();
        if (!$resourceId) {
            $message[] = sprintf(
                'Resource "%s" is not created.',
                $resource['name']
            );
            return false;
        }
        */

        return true;
    }

    /**
     * Delete a page
     * @param int|Pi\Db\RowGateway\RowGateway $page
     * @param array $message
     * @return bool
     */
    protected function deletePage($page, &$message)
    {
        $modelPage = Pi::model('page');
        //$modelResource = Pi::model('permission_resource');

        if (is_scalar($page)) {
            $pageRow = $modelPage->find($page);
        } else {
            $pageRow = $page;
        }
        $pageRow->delete();
        /*
        if (empty($pageRow['controller'])) {
            return true;
        }
        $name = $pageRow['module'] . '-' . $pageRow['controller'];
        if ($pageRow['action']) {
            $name .= '-' . $pageRow['action'];
        }
        $where = array(
            'section'   => $pageRow->section,
            'module'    => $pageRow->module,
            'type'      => 'page',
            'name'      => $name
        );
        $resourceRow = $modelResource->select($where)->current();
        if ($resourceRow) {
            $resourceRow->delete();
        }
        */

        return true;
    }
}
