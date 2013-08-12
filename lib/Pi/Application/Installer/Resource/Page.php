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
use Pi\Acl\Acl as AclHandler;

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
 *                  // 1 - specified; 0 - inherite from parent
 *                  'block'         => 0,
 *                  // Permission with specific role access rules
 *                  'permission'    => array(
 *                      'access'        => array(
 *                          'guest'     => 1,
 *                          'member'    => 0,
 *                      ),
 *                  ),
 *              ),
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *                  'cache_ttl'     => 60,
 *                  'cache_level'   => 'role',
 *                  // Block inheritance:
 *                  // 1 - specified; 0 - inherite from parent
 *                  'block'         => 1,
 *                  // Don't set permission
 *                  'permission'    => false,
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
 *                  // Permission with specific role access rules
 *                  'permission'    => array(
 *                      'access'        => array(
 *                          'rolea'     => 1,
 *                          'roleb'    => 0,
 *                      ),
 *                  ),
 *              ),
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *                  'cache_ttl'  => 0,
 *                  'cache_level'   => '',
 *                  // Permission with named parent resource
 *                  'permission'    => array(
 *                      'parent'        => 'admin,
 *                  ),
 *              ),
 *              array(
 *                  'title'         => 'Title',
 *                  'controller'    => 'controllerName',
 *                  'action'        => 'actionName',
 *                  'cache_ttl'  => 0,
 *                  'cache_level'   => '',
 *                  // Permission with dispatchable parent resource
 *                  // or named resource from different section/module
 *                  'permission'    => array(
 *                      'parent'        => array(
 *                          'section'   => 'admin',
 *                          'module'    => 'system',
 *                          'name'      => 'admin',
 *                      ),
 *                  ),
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
     * Canonize page config
     *
     * @param array $config
     * @return array
     */
    protected function canonizePage($config)
    {
        $moduleTitle = $this->event->getParam('title');
        $pageEntry = array(
            'title' => $moduleTitle . ' *',
        );
        // Set module exception for admin
        if (empty($config['admin']) && !isset($config['exception'])) {
            $config['exception'] = array($pageEntry);
        }
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

        return $config;
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
        $pages = $this->canonizePage($this->config);

        foreach (array_keys($pages) as $section) {
            // Skip the section if disabled
            if ($pages[$section] === false) continue;
            $pageList = array();
            foreach ($pages[$section] as $key => $page) {
                $page['section'] = $section;
                $page['module'] = $module;
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
            $diablePage = true;
        } else {
            $pages = $this->canonizePage($this->config);
            $diablePage = false;
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
                $page['section'] = $section;
                $page['module'] = $module;
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
            if ($page['custom'] && !$diablePage) continue;
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
        $module = $this->event->getParam('module');
        $modelPage = Pi::model('page');
        $modelResource = Pi::model('acl_resource');
        $modelRule = Pi::model('acl_rule');
        $columnsPage = array(
            'title',
            'section', 'module', 'controller', 'action',
            'cache_ttl', 'cache_level', 'block', 'custom'
        );
        $columnsResource = array(
            'section', 'name', 'item', 'title',
            'module', 'type'
        );

        $data = array();
        foreach ($page as $col => $val) {
            if (in_array($col, $columnsPage)) {
                $data[$col] = $val;
            }
        }
        $message = array();
        // Insert page
        $pageRow = $modelPage->createRow($data);
        $pageRow->save();
        if (!$pageRow->id) {
            $message[] = sprintf('Page "%s" is not saved.', $data['title']);
            return false;
        }

        // Set up permission resource

        // If permission is disabled explicitly, skip
        if (isset($page['permission']) && $page['permission'] === false) {
            return true;
        }

        $resource = array();
        foreach ($page as $col => $val) {
            if (in_array($col, $columnsResource)) {
                $resource[$col] = $val;
            }
        }
        $resource['id'] = null;
        $resource['name'] = $pageRow->id;
        $resource['type'] = 'page';
        $parent = 0;
        // Set parent by named resource
        if (!empty($page['permission']['parent'])) {
            if (is_string($page['permission']['parent'])) {
                $where = array(
                    'section'   => $resource['section'],
                    'module'    => $resource['module'],
                    'name'      => $page['permission']['parent']
                );
            // use parent params if available
            } elseif (is_array($page['permission']['parent'])) {
                $where = array_merge(array(
                    'section'   => $resource['section'],
                    'module'    => $resource['module'],
                ), $page['permission']['parent']);
            }
            $parent = $modelResource->select($where)->current();
        // Set parent by module-controller-action
        } elseif (!empty($page['controller'])) {
            $where = array(
                'section'   => $resource['section'],
                'module'    => $resource['module'],
            );
            if (!empty($page['action'])) {
                $where['controller'] = $page['controller'];
            }
            $parentPage = $modelPage->select($where)->current();
            if ($parentPage) {
                $where = array(
                    'section'   => $page['section'],
                    'name'      => $parentPage->id
                );
                $parent = $modelResource->select($where)->current();
            }
        }

        // Add resource
        $resourceId = $modelResource->add($resource, $parent);
        if (!$resourceId) {
            $message[] = sprintf(
                'Resource "%s" is not created.',
                $resource['name']
            );
            return false;
        }
        // Set rules of accessing the resource by each role
        if (isset($page['permission']['access'])) {
            foreach ($page['permission']['access'] as $role => $rule) {
                AclHandler::addRule(
                    $rule,
                    $role,
                    $resource['section'],
                    $module,
                    $resourceId
                );
            }
        }

        return true;
    }

    /**
     * Delete a page
     * @param int|Pi\Application\Model\Model $page
     * @param array $message
     * @return bool
     */
    protected function deletePage($page, &$message)
    {
        $modelPage = Pi::model('page');
        $modelResource = Pi::model('acl_resource');
        $modelRule = Pi::model('acl_rule');

        if (is_scalar($page)) {
            $pageRow = $modelPage->find($page);
        } else {
            $pageRow = $page;
        }
        $pageRow->delete();
        $where = array(
            'section'   => $pageRow->section,
            'module'    => $pageRow->module,
            'type'      => 'page',
            'name'      => $pageRow->id
        );
        $resourceRow = $modelResource->select($where)->current();
        if (!$resourceRow) {
            //trigger_error('Resource for page ' . $pageRow->id
            //. ' is not found, perhaps it is deleted by'
            //. ' its parent page resource.',
            //E_USER_NOTICE);
            return;
        }
        $resourceRows = $modelResource->getChildren($resourceRow, array('id'));
        $resources = array();
        foreach ($resourceRows as $row) {
            $resources[] = $row->id;
        }
        $modelRule->delete(array(
            'section'   => $pageRow->section,
            'resource'  => $resources,
        ));
        $modelResource->remove($resourceRow, true);

        //$pageRow->delete();

        return true;
    }
}
