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
 * @package         Module\System
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Module\System\Installer\Action;
use Pi;
use Pi\Application\Installer\Action\Uninstall as BasicUninstall;
use Zend\EventManager\Event;

class Uninstall extends BasicUninstall
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.pre', array($this, 'checkModules'), 1000);
        $events->attach('install.post', array($this, 'dropTables'), -1000);
        parent::attachDefaultListeners();
        return $this;
    }

    public function checkModules(Event $e)
    {
        $module = $this->event->getParam('module');
        $model = Pi::mdel('module');
        $rowset = $model->select(array('dirname <> ?' => $module));
        if ($rowset->count() > 0) {
            $result = array(
                'status'    => false,
                'message'   => 'Modules are not unistalled completely.'
            );
            $e->setParam('result', $result);
            return false;
        }
        return true;
    }

    public function dropTables(Event $e)
    {
        $module = $this->event->getParam('module');
        $modelTable = Pi::model('module_schema');
        $rowset = $modelTable->select(array('module' => $module));
        foreach ($rowset as $row) {
            Pi::db()->adapter()->query('DROP TABLE IF EXISTS ' . Pi::db()->prefix($row->name, ''), 'execute');
        }
        return;
    }
}
