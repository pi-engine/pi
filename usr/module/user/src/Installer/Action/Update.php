<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace   Module\User\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Module\User\Installer\Schema;
use Zend\EventManager\Event;

/**
 * Module update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        $events->attach('update.post', array($this, 'updatePostSchema'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Update module table schema
     *
     * @param Event $e
     * @return bool
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');
        $updator = new Schema\Updator160($this);
        $result = $updator->upgrade($moduleVersion);

        return $result;
    }
    
    public function updatePostSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');
        $updator = new Schema\UpdatorPost160($this);
        $result = $updator->upgrade($moduleVersion);

        return $result;
    }
}
