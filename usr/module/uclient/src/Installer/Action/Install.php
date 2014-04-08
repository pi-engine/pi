<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Uclient\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicAction;
use Pi\Application\Installer\Module as ModuleInstaller;
use Pi\Application\Installer\Resource\User as UserResource;
use Zend\EventManager\Event;

/**
 * Install handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Install extends BasicAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.pre', array($this, 'checkConflicts'), 10);
        $events->attach('install.post', array($this, 'updateConfig'), 1);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Check other modules in conflict
     *
     * @param Event $e
     * @return bool
     */
    public function checkConflicts(Event $e)
    {
        $modules = Pi::registry('module')->read();
        if (isset($modules['user'])) {
            $this->setResult('uclient', array(
                'status'    => false,
                'message'   => 'The module can not co-exist with user module',
            ));

            return false;
        }

        return true;
    }

    /**
     * Update user service config
     *
     * @param Event $e
     *
     * @return bool
     */
    public function updateConfig(Event $e)
    {
        $config = Pi::config()->load('service.user.php', false);
        $config['adapter'] = 'client';
        Pi::config()->write('service.user.php', $config, true);

        $config = Pi::config()->load('module.uclient.php', false);
        Pi::config()->write('module.uclient.php', $config, true);

        $config = Pi::config()->load('service.authentication.php', false);
        $config['strategy'] = 'client';
        Pi::config()->write('service.authentication.php', $config, true);

        $config = Pi::config()->load('service.avatar.php', false);
        $newConfig = array(
            'adapter' => 'client',
            'size_map'  => $config['size_map'],
        );
        Pi::config()->write('service.avatar.php', $newConfig, true);

        return true;
    }
}
