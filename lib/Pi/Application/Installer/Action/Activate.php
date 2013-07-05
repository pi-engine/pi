<?php
/**
 * Pi module activate action
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


class Activate extends AbstractAction
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('activate.pre', array($this, 'checkIndependent'));
        $events->attach('activate.post', array($this, 'createDependency'));
        return $this;
    }

    public function process()
    {
        $model = Pi::model('module');
        $row = $model->select(array('name' => $this->module))->current();
        $row->active = 1;
        // save module entry into database
        if (!$row->save()) {
            $this->setResult('module', array(
                'status'    => false,
                'message'   => array('Module activate failed')
            ));
            return false;
        }

        $this->event->setParam('row', $row);
        return true;
    }

    public function rollback()
    {
        $row = $this->event->getParam('row');
        $row->active = 0;
        return $row->save();
    }
}
