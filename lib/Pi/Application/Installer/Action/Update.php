<?php
/**
 * Pi module update action
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

class Update extends AbstractAction
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        if ($this->event->getParam('upgrade')) {
            $events->attach('update.post', array($this, 'removeDependency'));
            $events->attach('update.post', array($this, 'createDependency'));
        }
        return $this;
    }

    public function process()
    {
        $model = Pi::model('module');
        $row = $model->select(array('name' => $this->event->getParam('module')))->current();

        $config = $this->event->getParam('config');
        $configVersion = $config['meta']['version'];
        if (version_compare($row->version, $configVersion, '>=')) {
            $row->update = time();
            $row->save();
            return true;
        } else {
            $this->event->setParam('upgrade', true);
        }

        $originalRow = clone $row;
        $config = $this->event->getParam('config');
        $meta = array('update' => time());
        $moduleColumns = array('id', 'name', 'title', 'directory', 'version', 'update', 'active');
        foreach ($config['meta'] as $key => $value) {
            if (in_array($key, $moduleColumns)) {
                $meta[$key] = $value;
            }
        }
        //$meta['active'] = 1;
        $row->assign($meta);

        // save module entry into database
        if (!$row->save()) {
            $this->setResult('module', array(
                'status'    => false,
                'message'   => array('Module upgrade failed')
            ));
            return false;
        }

        $this->event->setParam('row', $originalRow);

        return true;
    }

    public function rollback()
    {
        $row = $this->event->getParam('row');
        if ($row) {
            $row->save();
        }
        return;
    }
}
