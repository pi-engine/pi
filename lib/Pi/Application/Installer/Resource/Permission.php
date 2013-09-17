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
use Pi\Application\Bootstrap\Resource\AdminMode;

/**
 * Setup permission component with configuration specs
 *
 * ```
 *          // Callback for front resources
 *          'callback'  => 'Module\<Module-name>\Permission\<CallbackName>',
 *
 *          // Front resources
 *          'front'    => array(
 *              // Only underscore, alphabetic and int allowed for resource key
 *              <resource-key>  => array(
 *                  'title'     => 'Resource Title',
 *                  'access'    => array(
 *                      'guest'     => 1,
 *                      'member'    => 1
 *                  ),
 *              ),
 *              <...>
 *          ),
 *
 *          // Admin resources
 *          'admin' => array(
 *              <...>
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
        $result = array();
        //$module = $this->getModule();
        if (isset($config['callback'])) {
            $resource = array(
                'section'   => 'front',
                'type'      => 'callback',
                'name'      => $config['callback'],
            );
            $config['callback'] = $resource;
        }
        foreach ($config as $section => &$resourceList) {
            foreach ($resourceList as $name => &$resource) {
                $name = preg_replace('/[^a-z0-9_]/i', '_', $name);
                if (!isset($resource['name'])) {
                    $resource['name'] = $name;
                }
                $resource['section'] = $section;
                $resource = $this->canonizeResource($resource);
                $result[$section][$name] = $resource;
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
                'moderator' => array(
                    'webmaster',
                ),
            ),
            'admin' => array(
                AdminMode::MODE_ADMIN   => array(
                    'admin',
                ),
                AdminMode::MODE_SETTING => array(
                    'admin',
                ),
            ),
        );
        // Add module permission rules
        foreach ($modulePerms as $section => $access) {
            foreach ($access as $role) {
                $resource = array(
                    'section'   => $section,
                    'module'    => $module,
                    'resource'  => 'module-' . $access,
                );
                Pi::service('permission')->grantPermission($role, $resource);
            }
        }
        Pi::registry('moduleperm')->flush();

        if (empty($this->config)) {
            return true;
        }

        $config = $this->canonize($this->config);

        // Add resources
        $model = Pi::model('permission_resource');
        foreach ($config as $section => $resourceList) {
            foreach ($resourceList as $name => $resource) {
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
            'type'      => array('system', 'callback'),
        ));
        // Find existent resources
        $resourcesExist = array();
        foreach ($rowset as $row) {
            $resourcesExist[$row->section][$row->name] = $row;
        }

        foreach ($config as $section => $resourceList) {
            foreach ($resourceList as $name => $resource) {
                // Update existent resource
                if (isset($resourcesExist[$section][$name])) {
                    $row = $resourcesExist[$section][$name];
                    $row->assign($resource);
                    $status = $row->save();
                    $message = array();
                    if (!$status) {
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
            'type'      => array('system', 'callback')
        ));
        Pi::model('permission_rule')->delete(array('module' => $module));

        Pi::registry('moduleperm')->flush();
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
