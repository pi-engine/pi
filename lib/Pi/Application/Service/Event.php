<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Event service
 *
 * - Trigger an event with event name:
 *
 * ```
 *  Pi::service('event')->trigger(
 *      <event-name>[, <data-for-the-event>[, <shortcircuit-callback>]]
 *  );
 * ```
 *
 * - Trigger an event with module and event name:
 *
 * ```
 *  Pi::service('event')->trigger(
 *      array(<module-name>, <event-name>)
 *      [, <data-for-the-event>[, <shortcircuit-callback>]]
 *  );
 * ```
 *
 * - Attach a listener in run-time
 *
 * ```
 *  Pi::service('event')->attach(<module-name>, <event-name>,
 *      array(<callback-class>, <callback-method>[, <callback-module-name>]));
 * ```
 *
 * @see Pi\Application\Installer\Resource\Event for event specifications
 * @see Pi\Application\Registry\Event for event listing
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Event extends AbstractService
{
    /**
     * Run-time attached listeners
     *
     * @var array
     */
    protected $container;

    /**
     * Trigger (or notify) callbacks registered to an event
     *
     * @param string|array  $event          Event name or module event pair
     * @param mixed|null    $object         Object or array
     * @param Callback|null $shortcircuit   Callback to stop the event trigger
     * @return bool
     */
    public function trigger($event, $object = null, $shortcircuit = null)
    {
        if (is_array($event)) {
            list($module, $event) = $event;
        } else {
            $module = Pi::service('module')->current();
        }

        // Load pre-defined listeners
        $listeners = $this->loadListeners($module, $event);
        $isStopped = false;
        foreach ($listeners as $listener) {
            $moduleName = array_pop($listener);
            $result = call_user_func_array($listener,
                array($object, $moduleName));
            if ($shortcircuit) {
                $status = call_user_func($shortcircuit, $result);
                if ($status) {
                    $isStopped = true;
                    break;
                }
            }
        }

        // Load run-time attached listeners
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
        return Pi::registry('event')->read($module, $event);
    }

    /**
     * Attach a predefined observer to an event in run-time
     *
     * @param string    $module     Event module
     * @param string    $event      Event name
     * @param array     $listener   Listener callback:
     *      <class>, <method>[, <module>]
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
     * @param string        $module     Event module
     * @param string        $event      Event name
     * @param array|null    $listener   Listener callback:
     *      <class>, <method>, <module>
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
