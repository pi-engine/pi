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
 * Event/Listener setup
 *
 * Event specifications:
 *
 * ```
 * array(
 *  // Event list
 *  'event'    => array(
 *      // event name (unique)
 *      'user_call' => array(
 *          // title
 *          'title' => Pi::_('Event hook demo'),
 *      ),
 *  ),
 *
 *  // Listener list
 *  'listener' => array(
 *      array(
 *          // event info: module, event name
 *          'event'     => array('pm', 'test'),
 *          // listener callback: class, method
 *          'callback'  => array('event', 'message'),
 *      ),
 *  ),
 * );
 * ```
 *
 * API for listener callback:
 *
 * ```
 *  class <ListenerClass>
 *  {
 *      static public function <listenerMethod>(<object>[, <module-name>])
 *      {
 *          // Do something;
 *      }
 *  }
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Event extends AbstractResource
{
    /**
     * Canonize event spec data
     *
     * @param array $config
     * @return array
     */
    protected function canonize(array $config)
    {
        $result = array(
            'event'     => array(),
            'listener'  => array(),
        );

        if (isset($config['event'])) {
            $events = $config['event'];
        } elseif (isset($config['events'])) {
            $events = $config['events'];
        } else {
            $events = array();
        }
        if (isset($config['listener'])) {
            $listeners = $config['listener'];
        } elseif (isset($config['listeners'])) {
            $listeners = $config['listeners'];
        } else {
            $listeners = array();
        }
        $module = $this->event->getParam('module');
        foreach ($events as $key => $event) {
            if (!isset($event['module'])) {
                $event['module'] = $module;
            }
            if (!isset($event['name'])) {
                $event['name'] = $key;
            }
            $result['event'][$event['name']] = $event;
        }
        foreach ($listeners as $listener) {
            list($eventModule, $eventName) = $listener['event'];
            $callback = !empty($listener['callback'])
                ? $listener['callback'] : $listener['listener'];
            list($class, $method) = $callback;
            $data = array();
            $data['event_module']   = $eventModule;
            $data['event_name']     = $eventName;
            $data['module']         = $module;
            $data['class']          = ucfirst($class);
            $data['method']         = $method;

            $result['listener'][] = $data;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }
        $module = $this->event->getParam('module');
        Pi::registry('event')->clear($module);
        $config = $this->canonize($this->config);
        // Install events
        $modelEvent = Pi::model('event');
        $events = $config['event'];
        foreach ($events as $name => $event) {
            $status = $modelEvent->insert($event);
            if (!$status) {
                $message = 'Event "%s" is not created.';
                return array(
                    'status'    => false,
                    'message'   => sprintf($message, $name),
                );
            }
        }

        // Install listeners
        $listeners = $config['listener'];
        $flushList = array();
        $modelListener = Pi::model('event_listener');
        foreach ($listeners as $listener) {
            $status = $modelListener->insert($listener);
            if (!$status) {
                $message = 'Listener for event "%s" is not created.';
                return array(
                    'status'    => false,
                    'message'   => srpintf($message, $listener['event_name']),
                );
            }
            $flushList[$listener['event_module']] = 1;
        }
        foreach (array_keys($flushList) as $moduleName) {
            Pi::registry('event')->clear($moduleName);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('event')->clear($module);

        if ($this->skipUpgrade()) {
            return;
        }

        $config = $this->canonize($this->config);
        $modelEvent = Pi::model('event');
        $modelListener = Pi::model('event_listener');

        $events = $config['event'];
        $eventList = $modelEvent->select(array('module' => $module));
        foreach ($eventList as $row) {
            // Delete deprecated events
            if (!isset($events[$row->name])) {
                $row->delete();
                $status = true;
                if (!$status) {
                    $message = 'Deprecated event "%s" is not deleted.';
                    return array(
                        'status'    => false,
                        'message'   => sprintf($message, $row->name),
                    );
                }
                // Delete listeners
                $modelListener->delete(array(
                    'event_name' => $row->name,
                    'event_module' => $row->module
                ));
                $status = true;
                if (!$status) {
                    $message = 'Listeners for deprecated event "%s"'
                             . ' are not deleted.';
                    return array(
                        'status'    => false,
                        'message'   => sprintf($message, $row->name),
                    );
                }
                continue;
            }
            // Update event
            if ($row->title != $events[$row->name]['title']) {
                $row->title = $events[$row->name]['title'];
                try {
                    $row->save();
                } catch (\Exception $e) {
                    $message = 'Event "%s" is not updated.';
                    return array(
                        'status'    => false,
                        'message'   => sprintf($message, $row->name)
                    );
                }
            }
            unset($events[$row->name]);
        }
        // Add new events
        foreach ($events as $name => $event) {
            $row = $modelEvent->createRow($event);
            $status = $row->save();
            if (!$status) {
                $message = 'Event "%s" is not created.';
                return array(
                    'status'    => false,
                    'message'   => sprintf($message, $name),
                );
            }
        }

        $listeners = $config['listener'];
        $listenerList = array();
        foreach ($listeners as $listener) {
            $key = $listener['event_module'] . '-' . $listener['event_name']
                 . '-' . $listener['class'] . '-' . $listener['method'];
            $listenerList[$key] = $listener;
        }

        $rowset = $modelListener->select(array('module' => $module));
        $flushList = array();
        foreach ($rowset as $row) {
            $key = $row['event_module'] . '-' . $row['event_name']
                 . '-' . $row['class'] . '-' . $row['method'];

            // Delete deprecated listeners
            if (!isset($listenerList[$key])) {
                $row->delete();
                $status = true;
                /*
                if (!$status) {
                    $message = 'Deprecated listener "%s" is not deleted.';
                    return array(
                        'status'    => false,
                        'message'   => sprintf($message, $key),
                    );
                }
                */
                $flushList[$row['event_module']] = 1;
            // Skip existent listeners
            } else {
                unset($listenerList[$key]);
            }
        }

        // Add new listeners
        foreach ($listenerList as $key => $data) {
            //$data = $this->canonize($data);
            $status = $modelListener->insert($data);
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => sprintf(
                        'Listener "%s" is not created.',
                        $key
                    )
                );
            }
            $flushList[$data['event_module']] = 1;
        }
        foreach (array_keys($flushList) as $moduleName) {
            Pi::registry('event')->clear($moduleName);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('event')->clear($module);

        $modelEvent = Pi::model('event');
        $modelListener = Pi::model('event_listener');
        $modelEvent->delete(array('module' => $module));
        $rowset = $modelListener->select(array('module' => $module));
        $modelListener->delete(array('module' => $module));
        foreach ($rowset as $row) {
            Pi::registry('event')->clear($row->event_module);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('event')->clear($module);

        $modelEvent = Pi::model('event');
        $modelEvent->update(array('active' => 1), array('module' => $module));
        $modelListener = Pi::model('event_listener');
        $modelListener->update(
            array('active' => 1),
            array('module' => $module)
        );
        $rowset = $modelListener->select(array('module' => $module));
        foreach ($rowset as $row) {
            Pi::registry('event')->clear($row['event_module']);
        }
        Pi::registry('event')->clear($module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('event')->clear($module);

        $modelEvent = Pi::model('event');
        $modelEvent->update(array('active' => 0), array('module' => $module));
        $modelListener = Pi::model('event_listener');
        $modelListener->update(
            array('active' => 0),
            array('module' => $module)
        );
        $rowset = $modelListener->select(array('module' => $module));
        foreach ($rowset as $row) {
            Pi::registry('event')->clear($row['event_module']);
        }
        Pi::registry('event')->clear($module);

        return true;
    }
}
