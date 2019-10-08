<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Activate as BasicAction;
use Zend\EventManager\Event;

/**
 * Uninstall handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Activate extends BasicAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('activate.post', [$this, 'updateConfig'], 1);
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
        $config            = Pi::config()->load('service.user.php', false);
        $config['adapter'] = 'local';
        Pi::config()->write('service.user.php', $config, true);
        Pi::service('user')->reload($config);

        return true;
    }
}
