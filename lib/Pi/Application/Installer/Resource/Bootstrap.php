<?php
/**
 * Pi module installer resource
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

namespace Pi\Application\Installer\Resource;
use Pi;

class Bootstrap extends AbstractResource
{
    protected function canonize($data)
    {
        $config = array(
            'module'    => $this->event->getParam('module'),
            'priority'  => 1,
            'active'    => 1,
        );
        if (is_scalar($data)) {
            $config['priority'] = intval($data);
        } elseif (is_array($data) && isset($data['priority'])) {
            $config['priority'] = intval($data['priority']);
        }

        return $config;
    }

    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }
        $module = $this->event->getParam('module');
        Pi::service('registry')->bootstrap->clear($module);

        $model = Pi::model('bootstrap');
        $data = $this->canonize($this->config);
        $model->insert($data);

        return true;
    }

    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->bootstrap->clear($module);
        if ($this->skipUpgrade()) {
            return;
        }

        $model = Pi::model('bootstrap');
        $row = $model->select(array('module' => $module))->current();
        if (empty($this->config)) {
            if ($row) {
                $row->delete();
            }
            return true;
        }
        $data = $this->canonize($this->config);
        if ($row) {
            $status = $model->update($data, array('id' => $row->id));
        } else {
            $status = $model->insert($data);
        }

        return true;
    }

    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->bootstrap->clear($module);

        $model = Pi::model('bootstrap');
        $model->delete(array('module' => $module));
        return true;
    }
}
