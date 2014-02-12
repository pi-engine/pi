<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer\Resource;

use Pi;

/**
 * I18n setup
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class I18n extends AbstractResource
{
    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('i18n')->clear($module);
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        if ('system' == $module) {
            Pi::registry('i18n')->flush();
        } else {
            Pi::registry('i18n')->clear($module);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('i18n')->clear($module);
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('i18n')->clear($module);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('i18n')->clear($module);
    }
}
