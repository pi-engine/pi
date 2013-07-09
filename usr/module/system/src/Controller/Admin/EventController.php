<?php
/**
 * System admin event controller
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
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Admin;

use Pi;
use Module\System\Controller\ComponentController  as ActionController;
use Zend\Db\Sql\Expression;

/**
 * Feature list:
 *  1. List of events and registered events of a module
 *  2. List of listeners and registered to events of a module
 *  3. Activate/deactivate an event
 *  4. Activate/deactivate an listener
 */
class EventController extends ActionController
{
    /**
     * List of event/listener sorted by module
     */
    public function indexAction()
    {
        // Module name, default as 'system'
        $name = $this->params('name', 'system');

        // Events of the module
        $events = array();
        $rowset = Pi::model('event')->select(array('module' => $name));
        $eventNames = array();
        foreach ($rowset as $row) {
            $events[$row->name] = array(
                'id'        => $row->id,
                'title'     => $row->title,
                'active'    => $row->active,
                'listeners' => array(),
            );
        }
        if ($events) {
            $rowset = Pi::model('event_listener')->select(array('event_module' => $name, 'event_name' => array_keys($events)));
            foreach ($rowset as $row) {
                $events[$row->event_name]['listeners'][] = array(
                    'id'        => $row->id,
                    'module'    => $row->module,
                    'class'     => $row->class,
                    'method'    => $row->method,
                    'active'    => $row->active,
                );
            }
        }

        // Listeners of the module
        $select = Pi::model('event_listener')->select()->where(array('module' => $name))->order(array('event_module', 'event_name'));
        $rowset = Pi::model('event_listener')->selectWith($select);
        $listeners = array();
        foreach ($rowset as $row) {
            $listeners[$row->id] = array(
                'id'        => $row->id,
                'active'    => $row->active,
                'title'     => sprintf('%s::%s', $row->class, $row->method),
                'event'     => sprintf('%s-%s', $row->event_module, $row->event_name),
            );
        }

        /*
        // Get module list
        $modules = array();
        $select = Pi::model('event')->select()->columns(array('module' => new Expression('DISTINCT module')));
        $rowset = Pi::model('event')->selectWith($select);
        $moduleList = array();
        foreach ($rowset as $row) {
            $moduleList[] = $row->module;
        }
        if ($moduleList) {
            $modules = Pi::model('module')->select(array('active' => 1, 'name' => $moduleList));
        }
        $this->view()->assign('modules', $modules);
        */

        $this->view()->assign('events', $events);
        $this->view()->assign('listeners', $listeners);
        $this->view()->assign('name', $name);
        //$this->view()->assign('title', sprintf(__('Events of module %s'), $name));

        //$this->view()->setTemplate('event-list');
    }

    /**
     * List of listener/event sorted by module
     */
    public function listenerAction()
    {
        // Module name, default as 'system'
        $name = $this->params('name', 'system');

        // Listeners of the module
        $select = Pi::model('event_listener')->select()->where(array('module' => $name))->order(array('event_module', 'event_name'));
        $rowset = Pi::model('event_listener')->selectWith($select);
        $listeners = array();
        foreach ($rowset as $row) {
            $listeners[$row->id] = array(
                'id'        => $row->id,
                'active'    => $row->active,
                'title'     => sprintf('%s::%s', $row->class, $row->method),
                'event'     => sprintf('%s-%s', $row->event_module, $row->event_name),
            );
        }

        // Get module list
        $modules = array();
        $select = Pi::model('event_listener')->select()->columns(array('module' => new Expression('DISTINCT module')));
        $rowset = Pi::model('event_listener')->selectWith($select);
        $moduleList = array();
        foreach ($rowset as $row) {
            $moduleList[] = $row->module;
        }
        if ($moduleList) {
            $modules = Pi::model('module')->select(array('active' => 1, 'name' => $moduleList));
        }

        $this->view()->assign('listeners', $listeners);
        $this->view()->assign('name', $name);
        $this->view()->assign('modules', $modules);
        $this->view()->assign('title', sprintf(__('Event listeners of module %s'), $name));

        $this->view()->setTemplate('event-listener');
    }

    /**
     * AJAX to Activate/Deactivate an event/listener
     */
    public function activeAction()
    {
        $status = 1;
        $message = '';

        $id = $this->params('id');
        $type = $this->params('type');
        if ('event' == $type) {
            $row = Pi::model('event')->find($id);
        } else {
            $row = Pi::model('event_listener')->find($id);
        }
        if (!$row) {
            $status = -1;
            $message = __('The item not found.');
        } else {
            // Disable
            if ($row->active) {
                $row->active = 0;
            // Enable
            } else {
                if (!Pi::service('module')->isActive($row->module)) {
                    $status = 0;
                } elseif ('listener' == $type &&  $row->event_module && !Pi::service('module')->isActive($row->event_module)) {
                    $status = 0;
                }
                if (!$status) {
                    $message = __('The item is not allowed to activate since module is inactive.');
                } else {
                    $row->active = 1;
                }
            }
            if ($status) {
                $row->save();
                $message = __('The item updated successfully.');

                $flush = 'listener' == $type ? $row->event_module : $row->module;
                Pi::service('registry')->event->clear($flush);
            }
        }
        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }
}
