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
use Pi\Application\Installer\Action\Uninstall as BasicAction;
use Laminas\EventManager\Event;

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
        $events->attach('uninstall.pre', [$this, 'removeCustom'], 1);
        $events->attach('uninstall.post', [$this, 'updateConfig'], 1);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Remove custom tables
     *
     * @param Event $e
     *
     * @return bool
     */
    public function removeCustom(Event $e)
    {
        $rowset = Pi::model('field', 'user')->select([]);
        foreach ($rowset as $row) {
            if ($row['handler']) {
                $handler = new $row['handler'];
                $handler->uninstall();
            }
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
        $config            = Pi::config()->load('service.user.php', false);
        $config['adapter'] = 'system';
        Pi::config()->write('service.user.php', $config, true);
        Pi::service('user')->reload($config);

        return true;
    }
}
