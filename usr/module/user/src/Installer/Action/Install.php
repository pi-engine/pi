<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Pi\Application\Installer\Module as ModuleInstaller;
use Zend\EventManager\Event;

class Install extends BasicInstall
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'checkProfile'), 1000);
        $events->attach('install.post', array($this, 'checkModules'), 1);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Prepare for account/profile/compound fields
     *
     * @param Event $e
     * @return void
     */
    public function checkProfile(Event $e)
    {

    }

    /**
     * Check other modules and install profiles if available
     *
     * @param Event $e
     * @return void
     */
    public function checkModules(Event $e)
    {

    }

}
