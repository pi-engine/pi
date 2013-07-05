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

class I18n extends AbstractResource
{
    public function installAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->i18n->clear($module);
    }

    public function updateAction()
    {
        $module = $this->event->getParam('module');
        if ('system' == $module) {
            Pi::service('registry')->i18n->flush();
        } else {
            Pi::service('registry')->i18n->clear($module);
        }
    }

    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->i18n->clear($module);
    }

    public function activateAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->i18n->clear($module);
    }

    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->i18n->clear($module);
    }
}
