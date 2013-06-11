<?php
/**
 * Pi module uninstall action
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


class Uninstall extends AbstractAction
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        //$events->attach('install.pre', array($this, 'loadConfig'));
        $events->attach('uninstall.pre', array($this, 'checkDependent'));
        $events->attach('uninstall.post', array($this, 'removeDependency'));
        //$events->attach('install.post', array($this->installer, 'updateMeta'));
        return $this;
    }

    public function process()
    {
        $result = $this->event->getParam('result');
        $model = Pi::model('module');
        $row = $model->select(array('name' => $this->module))->current();
        // save module entry into database
        if ($row) {
            $row->delete();
        }
        /*
        if (!$status) {
            $result['module'] = array(
                'status'    => false,
                'message'   => array('Module is failed to delete.')
            );
            $this->event->setParam('result', $result);
            return false;
        }
        */

        return true;
    }

}
