<?php
/**
 * Pi module installer resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Installer\Resource;

use Pi;

class I18n extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->i18n->clear($module);
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        if ('system' == $module) {
            Pi::service('registry')->i18n->flush();
        } else {
            Pi::service('registry')->i18n->clear($module);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->i18n->clear($module);
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->i18n->clear($module);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->i18n->clear($module);
    }
}
