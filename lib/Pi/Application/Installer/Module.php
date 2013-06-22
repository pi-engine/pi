<?php
/**
 * Pi module installer
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
use Pi\Db\RowGateway\RowGateway as ModuleRow;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;

/**
 * Maintenance of a module: install, uninstall, activate, deactivate, update
 *
 * Calling priority:
 *  \Module\Modulname\Installer\
 */
class Module
{
    protected $result;
    protected $options;
    protected $events;
    protected $event;

    /*
    const EVENT_INSTALL_PRE     = 'moudle.installer.install.pre';
    const EVENT_INSTALL_POST    = 'moudle.installer.install.post';
    const EVENT_UNINSTALL_PRE   = 'moudle.uninstaller.install.pre';
    const EVENT_UNINSTALL_POST  = 'moudle.uninstaller.install.post';
    const EVENT_UPDATE_PRE      = 'moudle.installer.install.pre';
    const EVENT_UPDATE_POST     = 'moudle.installer.install.post';
    const EVENT_ACTIVATE_PRE    = 'moudle.installer.install.pre';
    const EVENT_ACTIVATE_POST   = 'moudle.installer.install.post';
    const EVENT_DEACTIVATE_PRE  = 'moudle.installer.install.pre';
    const EVENT_DEACTIVATE_POST = 'moudle.installer.install.post';
    */

    public function __call($method, $args)
    {
        if (!in_array($method, array('install', 'uninstall', 'update', 'activate', 'deactivate'))) {
            throw new \InvalidArgumentException(sprintf('Invalid action "%s".', $method));
        }

        $model = null;
        $module = array_shift($args);
        $options = empty($args) ? array() : array_shift($args);
        $moduleVersion = isset($options['version']) ? $options['version'] : null;
        $moduleTitle = isset($options['title']) ? $options['title'] : '';
        if ($module instanceof ModuleRow) {
            $model = $module;
            $moduleName = $model->name;
            $moduleDirectory = $model->directory;
            $moduleTitle = $moduleTitle ?: $model->title;
            $moduleVersion = $moduleVersion ?: $model->version;
        } else {
            $moduleName = $module;
            $moduleDirectory = isset($options['directory']) ? $options['directory'] : $module;
        }
        $event = new Event;
        $event->setParams(array(
            'model'         => $model,
            'module'        => $moduleName,
            'directory'     => $moduleDirectory,
            'title'         => $moduleTitle,
            'version'       => $moduleVersion,
            'action'        => $method,
            'config'        => array(),
            'result'        => array(),
        ));
        $this->event = $event;
        $this->attachDefaultListeners();

        $this->getEventManager()->trigger('start', null, $event);

        $actionClass = sprintf('Module\\%s\Installer\Action\\%s', ucfirst($moduleDirectory), ucfirst($method));
        if (!class_exists($actionClass)) {
            $actionClass = sprintf('%s\Action\\%s', __NAMESPACE__, ucfirst($method));
        }
        $action = new $actionClass($event);
        $action->setEvents($this->getEventManager());

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
        $status = $action->process();
        if (!$status) {
            return false;
        }
        //$resourceHandler = new Resource($event);
        //$resourceHandler->attach($this->getEventManager());\
        $this->attachResource();
        $result = $this->getEventManager()->trigger('process', null, $event, $shortCircuit);
        if ($result->stopped()) {
            $action->rollback();
            return false;
        }

        $this->getEventManager()->trigger(sprintf('%s.post', $method), null, $event);
        $this->getEventManager()->trigger('finish', null, $event);

        $status = true;
        $result = $event->getParam('result');
        foreach ($result as $action => $state) {
            if ($state['status'] === false) {
                $status = false;
                break;
            }
        }
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
        $events->attach('finish', array($this, 'updateMeta'));
    }

    public function clearCache(Event $e)
    {
        Pi::persist()->flush();
        Pi::service('cache')->clearByNamespace($e->getParam('module'));
        Pi::service('registry')->module->clear($e->getParam('module'));
        Pi::service('registry')->modulelist->clear($e->getParam('module'));
    }

    public function getResult()
    {
        return $this->event->getParam('result');
    }

    public function renderMessage($message = null)
    {
        if (null === $message) {
            $message = $this->getResult();
        }
        $content = '';
        foreach ($message as $action => $state) {
            //$content .= '<p>';
            $content .= $action  . ': ' . (($state['status'] === false) ? 'failed' : 'passed');
            if (!empty($state['message'])) {
                $content .= '<br />&nbsp;&nbsp;' . implode('<br />&nbsp;&nbsp;', (array) $state['message']);
            }
            //$content .= '</p>';
        }
        return $content;
    }

    public function updateMeta(Event $e)
    {
        Pi::service('module')->createMeta();
        return true;
    }

    public function loadConfig(Event $e)
    {
        $config = Pi::service('module')->loadMeta($e->getParam('directory'));
        $e->setParam('config', $config);
        if (!$e->getParam('title')) {
            $e->setParam('title', $config['meta']['title']);
        }
    }

    protected function attachResource()
    {
        $resourceHandler = new Resource($this->event);
        $resourceHandler->attach($this->getEventManager());
    }
}
