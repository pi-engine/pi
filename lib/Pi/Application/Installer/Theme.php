<?php
/**
 * Pi theme installer
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Application
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Pi\Application\Installer;
use Pi;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;

/**
 * Theme maintenance
 *
 * @see Pi\Application\Service\Asset for asset maintenance
 */
class Theme
{
    protected $result;
    protected $options;
    protected $events;
    protected $event;
    protected $fileList = array(
        'front' => array(
            'template/layout-front.phtml',      // Complete layout template: header, footer, body, blocks, navigation
            'template/layout-simple.phtml',     // Simple page layout: header, footer, body
            'template/layout-style.phtml',      // Content with stylesheets
            'template/layout-content.phtml',    // Raw content

            'template/paginator.phtml',         // Pagination template

            'template/error-exception.phtml',   // Exception error template
            'template/error-404.phtml',         // 404 error template
            'template/error-denied.phtml',      // Denied error template

            'asset/css/style.css',              // Main css file
        ),
        'admin' => array(
            'template/layout-admin.phtml',      // Backoffice layout

            'template/paginator.phtml',         // Pagination template

            'template/error-exception.phtml',   // Exception error template
            'template/error-404.phtml',         // 404 error template
            'template/error-denied.phtml',      // Denied error template

            'asset/css/style.css',              // Main css file
        ),
    );

    public function __call($method, $args)
    {
        if (!in_array($method, array('install', 'uninstall', 'update'))) {
            throw new \InvalidArgumentException(sprintf('Invalid action "%s".', $method));
        }

        $name = array_shift($args);
        $options = empty($args) ? array() : array_shift($args);
        $version = isset($options['version']) ? $options['version'] : null;
        $event = new Event;
        $event->setParams(array(
            'name'          => $name,
            'version'       => $version,
            'action'        => $method,
            'config'        => array(),
            //'result'        => array(),
        ));
        $this->event = $event;
        $this->attachDefaultListeners();

        $this->getEventManager()->trigger('start', null, $event);

        // Define callback used to determine whether or not to short-circuit
        $shortCircuit = function ($r) {
            if (false === $r) {
                return true;
            }
            return false;
        };
        $result = $this->getEventManager()->trigger(sprintf('%s.pre', $method), null, $event, $shortCircuit);
        if ($result->stopped()) {
            return false;
        }
        $actionMethod = $method . 'Action';
        $result = $this->{$actionMethod}();
        if (!$result['status']) {
            $ret = array($method => $result);
            $this->event->setParam('result', $ret);
            return false;
        }
        $result = $this->getEventManager()->trigger('process', null, $event, $shortCircuit);
        if ($result->stopped()) {
            return false;
        }

        $this->getEventManager()->trigger(sprintf('%s.post', $method), null, $event);
        $this->getEventManager()->trigger('finish', null, $event);

        $status = true;
        $result = $event->getParam('result');
        //foreach ($result as $action => $state) {
            if ($result['status'] === false) {
                $status = false;
                //break;
            }
        //}
        return $status;
    }

    public function getEventManager()
    {
        if (!$this->events) {
            $this->events = new EventManager;
        }
        return $this->events;
    }

    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        $events->attach('start', array($this, 'loadConfig'));
        $events->attach('finish', array($this, 'clearCache'));
    }

    public function clearCache(Event $e)
    {
        Pi::service('registry')->theme->flush();
        Pi::service('registry')->themelist->flush();
    }

    public function getResult()
    {
        return $this->event->getParam('result');
    }

    public function renderMessage($message = null)
    {
        if (null === $message) {
            $message = (array) $this->getResult();
        }
        $content = '';
        foreach ($message as $action => $state) {
            $content .= '<p>';
            $content .= $action . ': ' . (($state['status'] === false) ? 'failed' : 'passed');
            if (!empty($state['message'])) {
                $content .= '<br />&nbsp;&nbsp;' . implode('<br />&nbsp;&nbsp;', (array) $state['message']);
            }
            $content .= '</p>';
        }
        return $content;
    }

    public function loadConfig(Event $e)
    {
        //$config = include Pi::path('theme') . '/' . $e->getParam('name') . '/config.php';
        $config = Pi::service('theme')->loadConfig($e->getParam('name'));
        $e->setParam('config', $config);
    }

    protected function canonizeData(array $data)
    {
        $return = array(
            'name'          => isset($data['name']) ? $data['name'] : $this->event->getParam('name'),
            'version'       => $data['version'],
            'update'        => isset($data['update']) ? $data['update'] : time(),
            'type'          => !empty($data['type']) ? $data['type'] : 'both',
            /*
            'title'         => $data['title'],
            'author'        => $data['author'],
            'screenshot'    => isset($data['screenshot']) ? $data['screenshot'] : '',
            */
        );
        return $return;
    }

    protected function installAction()
    {
        $name = $this->event->getParam('name');
        $config = $this->event->getParam('config');
        $type = isset($config['type']) ? $config['type'] : 'both';
        $result = array(
            'status'    => true,
            'message'   => ''
        );
        if (empty($config['parent']) && $files = $this->checkFiles($name, $type)) {
            $result = array(
                'status'    => false,
                'message'   => 'Files missing: ' . implode(' ', $files)
            );
        } else {
            $data = $this->canonizeData($config);
            $model = Pi::model('theme');
            $row = $model->createRow($data);
            $row->save();
            if (!$row->id) {
                $result = array(
                    'status'    => false,
                    'message'   => 'Theme row save failed.'
                );
                return $result;
            }
            $status = Pi::service('asset')->publish('theme/' . $name);
            if (!$status) {
                $result = array(
                    'status'    => false,
                    'message'   => 'Theme asset publish failed.'
                );
            } else {
                Pi::service('asset')->publishCustom($name);
            }
        }
        return $result;
    }

    protected function updateAction()
    {
        $name = $this->event->getParam('name');
        $version = $this->event->getParam('version');
        $config = $this->event->getParam('config');
        $status = true;
        $message = '';

        $row = Pi::model('theme')->find($name, 'name');
        $isUpgrade = version_compare($version, $config['version'], '<');
        if (!$isUpgrade) {
            $row->update = time();
            $row->save();
            $message = __('Theme updated successfully.');
        } else {
            $data = $this->canonizeData($config);
            $row->assign($data);
            $row->save();
            if (!$row->id) {
                $status = false;
                $message = __('Theme failed to update.');
            } else {
                $message = __('Theme upgraded successfully.');
                $res = Pi::service('asset')->publish('theme/' . $name);
                if (!$res) {
                    $status = false;
                    $message = __('Theme asset publish failed.');
                }
            }
        }
        Pi::service('asset')->removeCustom($name);
        Pi::service('asset')->publishCustom($name);

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    protected function uninstallAction()
    {
        $name = $this->event->getParam('name');
        if ('default' == $name) {
            throw new \Exception('Theme "default" is protected.');
        }
        $result = array(
            'status'    => true,
            'message'   => ''
        );
        $model = Pi::model('theme');
        $status = $model->delete(array('name' => $name));
        if (!$status) {
            $result = array(
                'status'    => false,
                'message'   => 'Theme uninstallation failed.'
            );
            return $result;
        }

        $status = Pi::service('asset')->remove('theme/' . $name);
        if (!$status) {
            $result = array(
                'status'    => false,
                'message'   => 'Theme assets removal failed.'
            );
        }
        Pi::service('asset')->removeCustom($name);

        return $result;
    }

    protected function checkFiles($theme, $type = 'both')
    {
        $fileList = $this->fileList;
        $path = Pi::path('theme/' . $theme);

        if (isset($fileList[$type])) {
            $files = $fileList[$type];
        } elseif ('both' == $type) {
            $files = array_unique(array_merge($fileList['front'], $fileList['admin']));
        } else {
            throw new \Exception(sprintf('Wrong type "%s" configured.', $type));
        }
        $missingFiles = array();
        foreach ($files as $file) {
            if (!file_exists($path . '/' . $file)) {
                $missingFiles[] = $file;
            }
        }

        return $missingFiles;
    }
}
