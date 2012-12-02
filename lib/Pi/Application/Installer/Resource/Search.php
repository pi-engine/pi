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

/**
 * Search configuration
 * array(
 *  'callback'  => array('class', 'method'),
 * );
 */
class Search extends AbstractResource
{
    public function installAction()
    {
        if (empty($this->config)) {
            return true;
        }
        $module = $this->event->getParam('module');
        Pi::service('registry')->search->clear($module);

        $model = Pi::model('search');
        $data = $this->config;
        $directory = $this->event->getParam('directory');
        $data['callback'][0] = sprintf('Module\\%s\\%s', ucfirst($directory), $data['callback'][0]);
        $data['module'] = $module;
        $row = $model->createRow($data);
        $row->save();
    }

    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->search->clear($module);
        if ($this->versionCompare()) {
            return true;
        }

        $model = Pi::model('search');
        $rowset = $model->select(array('module' => $module));
        $row = $rowset->current();
        if (empty($this->config)) {
            if ($row) {
                $row->delete();
            }
            return;
        }
        $data = $this->config;
        $directory = $this->event->getParam('directory');
        $data['callback'][0] = sprintf('Module\\%s\\%s', ucfirst($directory), $data['callback'][0]);
        $data['module'] = $module;
        if ($row) {
            $row->assign($data);
        } else {
            $row = $model->createRow($data);
        }
        $row->save();
    }

    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->search->clear($module);

        $model = Pi::model('search');
        $model->delete(array('module' => $module));
         return;
    }

    public function activateAction()
    {
        $module = $this->event->getParam('module');
        $model = Pi::model('search');
        $model->update(array('active' => 1), array('module' => $module));
        Pi::service('registry')->search->flush();
        return;
    }

    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        $model = Pi::model('search');
        $model->update(array('active' => 0), array('module' => $module));
        Pi::service('registry')->search->flush();
        return;
    }
}
