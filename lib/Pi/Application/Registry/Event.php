<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * Event/Listener list
 *
 * @see Pi\Application\Installer\Resource\Event for event specifications
 * @see Pi\Application\Service\Event for event trigger
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Event extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options)
    {
        $result = array(
            'event'     => '',
            'listener'  => array(),
        );
        /*
        $modelEvent = Pi::model('event');
        $count = $modelEvent->count(array(
            'module'    => $options['module'],
            'name'      => $options['event'],
            'active'    => 1
        ));
        if (!$count) {
            return $result;
        }
        */
        $result['event'] = $options['module'] . '-' . $options['event'];

        $modelListener = Pi::model('event_listener');
        $select = $modelListener->select()->where(array(
            'event_module'  => $options['module'],
            'event_name'    => $options['event'],
            'active'        => 1
        ));
        $listenerList = $modelListener->selectWith($select);
        //$directory = Pi::service('module')->directory($options['module']);
        $listeners = array();
        foreach ($listenerList as $row) {
            $module = $row['module'];
            if (false === strpos($row['class'], '\\')) {
                $class = sprintf(
                    'Custom\\%s\Api\\%s',
                    ucfirst($module),
                    ucfirst($row['class'])
                );
                if (!class_exists($class)) {
                    $directory = Pi::service('module')->directory($module);
                    $class = sprintf(
                        'Module\\%s\Api\\%s',
                        ucfirst($directory),
                        ucfirst($row['class'])
                    );
                }
            } else {
                $class = $row['class'];
            }
            $listeners[] = array($class, $row['method'], $module);
        }

        $result['listener'] = $listeners;

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param string    $module
     * @param string    $event
     */
    public function read($module = '', $event = '')
    {
        $module = $module ?: Pi::service('module')->current();
        if (empty($event)) return false;
        $options = compact('module', 'event');

        $data = $this->loadData($options);
        if (empty($data['event'])) {
            $result = false;
        } else {
            $result = (array) $data['listener'];
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @param string    $module
     * @param string    $event
     */
    public function create($module = '', $event = '')
    {
        $module = $module ?: Pi::service('module')->current();
        $this->clear($module);
        $this->read($module, $event);

        return true;
    }
}
