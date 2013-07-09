<?php
/**
 * Pi module install action
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

class Install extends AbstractAction
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        //$events->attach('install.pre', array($this, 'loadConfig'));
        $events->attach('install.pre', array($this, 'checkIndependent'));
        $events->attach('install.post', array($this, 'createDependency'));
        //$events->attach('install.post', array($this->installer, 'updateMeta'));
        return $this;
    }

    public function process()
    {
        $model = Pi::model('module');
        $moduleData = array(
            'name'          => $this->module,
            'directory'     => $this->directory,
            'title'         => $this->title ?: $this->config['meta']['title'],
            'version'       => $this->config['meta']['version'],
        );

        $row = $model->createRow($moduleData);
        // save module entry into database
        if (!$row->save()) {
            $this->setResult('module', array(
                'status'    => false,
                'message'   => array('Module insert failed')
            ));
            return false;
        }

        $this->event->setParam('row', $row);
        return true;
    }

    public function rollback()
    {
        $row = $this->event->getParam('row');
        return $row->delete();
    }
}
