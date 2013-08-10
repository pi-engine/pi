<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        $listeners = array();
        $modelEvent = Pi::model('event');
        $rowset = $modelEvent->select(array(
            'module'    => $options['module'],
            'name'      => $options['event'],
            'active'    => 1
        ));
        if ($rowset->count()) {
            return $listeners;
        }

        $modelListener = Pi::model('event_listener');
        $select = $modelListener->select()->where(array(
            'event_module'  => $options['module'],
            'event_name'    => $options['event'],
            'active'        => 1
        ));
        $listenerList = $modelListener->selectWith($select);
        $directory = Pi::service('module')->directory($options['module']);
        foreach ($listenerList as $row) {
            $class = sprintf(
                'Module\\%s\\%s',
                ucfirst($directory),
                ucfirst($class)
            );
            $listeners[] = array($class, $row->method, $row->module);
        }

        return $listeners;
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

        return $this->loadData($options);
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
