<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Even service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Event extends AbstractService
{
    /*
     * Run-time attached observers
     * @var array
     */
    protected $container;

    /**
     * Trigger (or notify) callbacks registered to an event
     *
     * @param string|array  $event  event name or module event pair
     * @param mixed         $object object or array
     * @param Callback|null $shortcircuit
     * @return bool
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
     * @param array     $callback: array of <class>, <method>, <module>
     * @return $this
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
     * @param array     $callback: array of <class>, <method>, <module>
     * @return $this
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
