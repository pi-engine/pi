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
//use Pi\Application\Bootstrap\Resource\AdminMode;

/**
 * Setup permission component with configuration specs
 *
 * ```
 *
 *          // Front resources
 *          'front'    => array(
 *              // Only underscore, alphabetic and int allowed for resource key
 *              <resource-key>  => array(
 *                  'title'     => 'Resource Title',
 *                  'access'    => array(
 *                      'guest',
 *                      'member'
 *                  ),
 *              ),
 *              <...>
 *              // Callback for custom resources
 *              // @see Pi\Application\Api\AbstractApi
 *              'custom'  => 'Module\<Module-name>\Api\<FrontCallbackName>',
 *          ),
 *
 *          // Admin resources
 *          'admin' => array(
 *              <...>
 *              // Callback for custom resources
 *              // @see Pi\Application\Api\AbstractApi
 *              'custom'  => 'Module\<Module-name>\Api\<AdminCallbackName>',
 *          ),
 *  );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

class Permission extends AbstractResource
{
    protected function canonize(array $config)
    {
        $adminMap = array(
            'front' => 'webmaster',
            'admin' => 'admin',
        );

        $result = array();
        //$module = $this->getModule();
        /*
        if (isset($config['custom'])) {
            $resource = array(
                'section'   => 'front',
                'type'      => 'custom',
                'name'      => $config['custom'],
            );
            $config['front']['custom'] = $resource;
            unset($config['custom']);
        }
        */

        foreach ($config as $section => &$resourceList) {
            foreach ($resourceList as $key => &$resource) {
                if ('custom' == $key && is_string($resource)) {
                    $resource= array(
                        'name'  => $resource,
                        'type'  => 'custom',
                    );
                }
                if (!isset($resource['name'])) {
                    $name = preg_replace('/[^a-z0-9_]/i', '_', $key);
                    $resource['name'] = $name;
                } else {
                    $name = $resource['name'];
                }
                $resource['section'] = $section;
                $access = empty($resource['access'])
                    ? array() : (array) $resource['access'];
                if (isset($adminMap[$section])) {
                    $access[] = $adminMap[$section];
                }
                $resource = $this->canonizeResource($resource);
                $result[$section][$name] = array(
                    'resource'  => $resource,
                    'access'    => array_unique($access),
                );
            }
        }

        return $result;
    }

    /**
     * Canonize resource config
     *
     * @param array $resource
     * @return array
     */
    protected function canonizeResource(array $resource)
    {
        $columns = array(
            'section', 'name', 'item', 'title', 'module', 'type'
        );

        if (!isset($resource['module'])) {
            $resource['module'] = $this->getModule();
        }
        if (!isset($resource['type'])) {
            $resource['type'] = 'system';
        }
        $data = array();
        foreach ($columns as $col) {
            if (isset($resource[$col])) {
                $data[$col] = $resource[$col];
            }
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->getModule();
        // Create module access permissions
        $modulePerms = array(
            'front' => array(
                'access' => array(
                    'guest',
                    'member',
                ),
                'admin' => array(
                    'webmaster',
                ),
            ),
            'admin' => array(
                'access'   => array(
                    'admin',
                ),
                'admin'   => array(
                    'admin',
                ),
            ),
        );
        // Add module permission rules
        foreach ($modulePerms as $section => $list) {
            foreach ($list as $access => $roles) {
                foreach ($roles as $role) {
                    $resource = array(
                        'section'   => $section,
                        'module'    => $module,
                        'resource'  => 'module-' . $access,
                    );
                    Pi::service('permission')->grantPermission($role, $resource);
                }
            }
        }

        if (empty($this->config)) {
            return true;
        }

        $config = $this->canonize($this->config);

        // Add resources
        $model = Pi::model('permission_resource');
        foreach ($config as $section => $resourceList) {
            foreach ($resourceList as $key => $data) {
                $resource = $data['resource'];
                $row = $model->createRow($resource);
                $status = $row->save();
                if (!$status) {
                    $message[] = sprintf(
                        'Resource "%s" is not created.',
                        $resource['name']
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                } elseif ($data['access']) {
                    foreach ($data['access'] as $role) {
                        $spec = array(
                            'section'   => $section,
                            'module'    => $module,
                            'resource'  => $resource['name'],
                        );
                        Pi::service('permission')->grantPermission($role, $spec);
                    }
                }
            }
        }

        Pi::registry('permission_resource')->flush();

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

        // Update resources
        $config = $this->canonize($this->config);
        $model = Pi::model('permission_resource');
        $rowset = $model->select(array(
            'module'    => $module,
            //'type'      => array('system', 'custom'),
        ));
        // Find existent resources
        $resourcesExist = array();
        foreach ($rowset as $row) {
            $resourcesExist[$row->section][$row->name] = $row;
        }

        foreach ($config as $section => $resourceList) {
            foreach ($resourceList as $key => $data) {
                $resource = $data['resource'];
                $name = $resource['name'];
                // Update existent resource
                if (isset($resourcesExist[$section][$name])) {
                    $row = $resourcesExist[$section][$name];
                    $row->assign($resource);
                    try {
                        $row->save();
                    } catch (\Exception $e) {
                        $message[] = sprintf(
                            'Resource "%s" is not updated.',
                            $resource['name']
                        );
                        return array(
                            'status'    => false,
                            'message'   => $message,
                        );
                    }
                    unset($resourcesExist[$section][$name]);
                    continue;
                }
                $message = array();
                // Add new resource
                $row = $model->createRow($resource);
                $status = $row->save();
                if (!$status) {
                    $message[] = sprintf(
                        'Resource "%s" is not created.',
                        $resource['name']
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                } elseif ($data['access']) {
                    foreach ($data['access'] as $role) {
                        $spec = array(
                            'section'   => $section,
                            'module'    => $module,
                            'resource'  => $resource['name'],
                        );
                        Pi::service('permission')->grantPermission($role, $spec);
                    }
                }
            }
        }

        // Remove not deprecated resources
        foreach ($resourcesExist as $section => $resourceList) {
            foreach ($resourceList as $name => $row) {
                $message = array();
                $status = $row->delete();
                if (!$status) {
                    $message[] = sprintf(
                        'Resource "%s" is not deleted.',
                        $name
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                } else {
                    $spec = array(
                        'section'   => $section,
                        'module'    => $module,
                        'resource'  => $row['name'],
                    );
                    Pi::service('permission')->revokePermission($spec);
                }
            }
        }

        Pi::registry('permission_resource')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');

        Pi::model('permission_resource')->delete(array(
            'module'    => $module,
            //'type'      => array('system', 'custom')
        ));
        Pi::model('permission_rule')->delete(array('module' => $module));

        Pi::registry('permission_resource')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        Pi::registry('permission_resource')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        Pi::registry('permission_resource')->flush();

        return true;
    }
}
