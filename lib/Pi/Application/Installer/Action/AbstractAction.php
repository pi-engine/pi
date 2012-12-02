<?php
/**
 * Pi module installer action
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

namespace Pi\Application\Installer\Action;
use Pi;
use Pi\Application\Installer\Module;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;

abstract class AbstractAction
{
    protected $events;
    protected $event;
    protected $config;
    protected $module;
    protected $directory;
    protected $title;

    public function __construct(Event $event)
    {
        $this->setEvent($event);
    }

    abstract public function process();

    public function rollback()
    {
        return true;
    }

    /*
    public function setInstaller(Module $installer)
    {
        $this->installer = $installer;
        return $this;
    }
    */

    public function setEvents(EventManager $events)
    {
        $this->events = $events;
        $this->attachDefaultListeners();
        return $this;
    }

    public function setEvent(Event $event)
    {
        $this->event        = $event;
        $this->config       = $event->getParam('config');
        $this->module       = $event->getParam('module');
        $this->directory    = $event->getParam('directory');
        $this->title        = $event->getParam('title') ?: $this->config['meta']['title'];
        return $this;
    }

    protected function attachDefaultListeners()
    {
    }

    public function checkDependent(Event $e)
    {
        //$options = $e->getParam('options');
        $model = Pi::model('module_dependency');
        $rowset = $model->select(array('independent' => $e->getParam('module')));
        if ($rowset->count() > 0) {
            $result = $e->getParam('result');
            $result['dependency'] = array(
                'status'    => false,
                'message'   => 'The module has dependants on it.'
            );
            $e->setParam('result', $result);
            return false;
        }
        return true;
    }

    public function checkIndependent(Event $e)
    {
        $config = $this->event->getParam('config');
        if (empty($config['dependency'])) {
            return true;
        }
        //$options = $e->getParam('options');
        $model = Pi::model('module_dependency');
        $rowset = $model->select(array('dependent' => $e->getParam('module')));
        $independents = $config['dependency'];
        $available = array();
        while ($row = $rowset->current()) {
            $available[] = $row->independent;
        }
        $missing = array_diff($independents, $available);
        if ($missing) {
            $result = $e->getParam('result');
            $result['dependency'] = array(
                'status'    => false,
                'message'   => 'Modules required by this module: ' . implode(', ', $missing)
            );
            $e->setParam('result', $result);
            return false;
        }
        return true;
    }

    public function createDependency(Event $e)
    {
        $config = $this->event->getParam('config');
        $module = $e->getParam('module');
        if (empty($config['dependency'])) {
            return true;
        }
        //$options = $e->getParam('options');
        $model = Pi::model('module_dependency');
        foreach ($config['dependency'] as $independent) {
            $row = $model->createRow(array(
                'dependent'     => $module,
                'independent'   => $independent
            ));
            if (!$row->save()) {
                $model->delete(array('dependent' => $module));
                $result = $e->getParam('result');
                $result['dependency'] = array(
                    'status'    => false,
                    'message'   => 'Module dependency is not built.'
                );
                $e->setParam('result', $result);
                return false;
            }
        }
        return true;
    }

    public function removeDependency(Event $e)
    {
        $config = $this->event->getParam('config');
        if (empty($config['dependency'])) {
            return true;
        }
        //$options = $e->getParam('options');
        $model = Pi::model('module_dependency');
        $ret = $model->delete(array('dependent' => $e->getParam('module')));
        if ($ret < count($config['dependency'])) {
            $result = $e->getParam('result');
            $result['dependency'] = array(
                'status'    => false,
                'message'   => 'Module dependency is not removed completely.'
            );
            $e->setParam('result', $result);
            return false;
        }
        return true;
    }
}
