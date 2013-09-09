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
use Pi\Application\Installer\Action\Uninstall as BasicAction;
use Zend\EventManager\Event;

/**
 * Uninstall handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Uninstall extends BasicAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('uninstall.post', array($this, 'updateConfig'), 1);
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
        $config = Pi::config()->load('service.user.php');
        $config['adapter'] = 'Pi\User\Adapter\System';
        Pi::config()->write('service.user.php', $config, true);

        return true;
    }
}
