<?php
/**
 * Installer Event class
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
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\Event;

class Resource implements ListenerAggregateInterface
{
    protected $listener;

    /**
     * Listeners we've registered
     *
     * @var array
     */
    //protected $listeners = array();
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Attach listeners
     *
     * @param  Events $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listener = $events->attach('process', array($this, 'processResources'));
    }

    /**
     * Detach listeners
     *
     * @param  Events $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        $events->detach($this->listener);
    }

    public function processResources(Event $e)
    {
        $this->event = $e;
        $result = $this->event->getParam('result');
        $resourceList = $this->resourceList();
        foreach ($resourceList as $resource) {
            $ret = $this->loadResource($resource);
            if (null === $ret) {
                continue;
            }
            $result['resource-' . $resource] = $ret;
            if (false === $ret['status']) {
                break;
            }
            if (Pi::service()->hasService('log')) {
                Pi::service('log')->info(sprintf('Module resource %s is loaded.', $resource));
            }
        }
        $this->event->setParam('result', $result);
        return;
    }

    protected function resourceList()
    {
        $resourceList = array();
        $iterator = new \DirectoryIterator(__DIR__ . "/Resource");
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $fileName = $fileinfo->getFilename();
            if (!preg_match("/^([^\.]+)\.php$/", $fileName, $matches)) {
                continue;
            }
            $resource = strtolower($matches[1]);
            if ($resource == "config" || $resource == 'abstractresource') {
                continue;
            }
            $resourceList[] = $resource;
        }
        $resourceList[] = "config";

        $config = $this->event->getParam('config');
        if (!empty($config['maintenance']['resource'])) {
            $resources = array_keys($config['maintenance']['resource']);
            //$resourceList = array_unique(array_merge($resourceList, $resources));
            $resourceList = array_unique(array_merge($resources, $resourceList));
        }

        return $resourceList;
    }

    /**
     * Load and performe resource actions
     *
     * @param strint $resource Resource name
     * @return array|null
     */
    protected function loadResource($resource)
    {
        $e = $this->event;
        $config = $e->getParam('config');
        $moduleDirectory = $e->getParam('directory');
        $resourceClass = sprintf('Module\\%s\Installer\Resource\\%s', ucfirst($moduleDirectory), ucfirst($resource));
        if (!class_exists($resourceClass)) {
            $resourceClass = sprintf('%s\Resource\\%s', __NAMESPACE__, ucfirst($resource));
        }
        if (!class_exists($resourceClass)) {
            return;
        }
        $methodAction = $e->getParam('action') . 'Action';
        if (!method_exists($resourceClass, $methodAction)) {
            return;
        }
        $options = isset($config['maintenance']['resource'][$resource]) ? $config['maintenance']['resource'][$resource] : array();
        if (is_string($options)) {
            $optionsFile = sprintf('%s/%s/config/%s', Pi::path('module'), $moduleDirectory, $options);
            $options = include $optionsFile;
            if (empty($options) || !is_array($options)) {
                $options = array();
            }
        }
        $resourceHandler = new $resourceClass($options);
        $resourceHandler->setEvent($this->event);
        $ret = $resourceHandler->$methodAction();

        if (is_string($ret)) {
            $ret = array(
                'status'    => true,
                'message'   => (array) $ret,
            );
        } elseif (is_bool($ret)) {
            $ret = array(
                'status'    => $ret,
                'message'   => array(),
            );
        } elseif (is_array($ret)) {
            if (!isset($ret['message'])) {
                $ret['message'] = array();
            } else {
                $ret['message'] = (array) $ret['message'];
            }
        } else {
            $ret = null;
        }

        if (null !== $ret) {
            array_unshift($ret['message'], 'Class: ' . $resourceClass);
        }

        return $ret;
    }
}
