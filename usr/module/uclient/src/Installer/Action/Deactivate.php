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
use Pi\Application\Installer\Action\Deactivate as BasicAction;
use Zend\EventManager\Event;

/**
 * Uninstall handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Deactivate extends BasicAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('deactivate.post', array($this, 'updateConfig'), 1);
        parent::attachDefaultListeners();

        return $this;
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
        $config['adapter'] = 'system';
        Pi::config()->write('service.user.php', $config, true);

        $file = Pi::config()->getPath('service.authentication.php', true);
        @unlink($file);

        $file = Pi::config()->getPath('service.avatar.php', true);
        @unlink($file);

        return true;
    }
}
