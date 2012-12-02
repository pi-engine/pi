<?php
/**
 * Pi Engine event service
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
 * @package         Pi\Application
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;
use Pi;

class Event extends AbstractService
{
    // Run-time attached observers
    protected $container;

    /**
     * Trigger (or notify) callbacks registered to an event
     *
     * @param string|array  $event  event name or module event pair
     * @param mixed  $object object or array
     * @param mixed        $shortcircuit
     * @return boolean
     */
    public function trigger($event, $object = null, $shortcircuit = null)
    {
        if (is_array($event)) {
            list($module, $event) = $event;
        } else {
            $module = Pi::service('module')->current();
        }
        $listeners = $this->loadListeners($module, $event);
        $isStopped = false;
        foreach ($listeners as $listener) {
            $moduleName = array_pop($listener);
            $result = call_user_func_array($listener, array($object, $moduleName));
            if ($shortcircuit) {
                $status = call_user_func($shortcircuit, $result);
                if ($status) {
                    $isStopped = true;
                    break;
                }
            }
        }
        if (!$isStopped && !empty($this->container[$module][$event])) {
            foreach ($this->container[$module][$event] as $key => $listener) {
                if (isset($listener[2])) {
                    $moduleName = array_pop($listener);
                    $data = array($object, $moduleName);
                } else {
                    $data = array($object);
                }
                $result = call_user_func_array($listener, $data);
                if ($shortcircuit) {
                    $status = call_user_func($shortcircuit, $result);
                    if ($status) {
                        break;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Load observers of an event
     *
     * @param string    $module
     * @param string    $event
     * @return array
     */
    public function loadListeners($module, $event)
    {
        return Pi::service('registry')->event->read($module, $event);
    }

    /**
     * Attach a predefined observer to an event in run-time
     *
     * @param string    $module
     * @param string    $event
     * @param array     $callback: array of ["class", "method", 'module']
     * @return boolean
     */
    public function attach($module, $event, $listener)
    {
        $key = implode('-', $listener);
        $this->container[$module][$event][$key] = $listener;
        return $this;
    }

    /**
     * Detach an observer from an event
     *
     * @param string    $module
     * @param string    $event
     * @param array     $callback: array of ["class", "method", 'module']
     * @return boolean
     */
    public function detach($module, $event, $listener = null)
    {
        if ($listener !== null) {
            $key = implode('-', $listener);
            $this->container[$module][$event][$key] = null;
        } else {
            $this->container[$module][$event] = null;
        }
        return $this;
    }
}
