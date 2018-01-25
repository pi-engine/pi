<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Installer\Action;

use Module\Article\Installer\Schema;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Zend\EventManager\Event;

/**
 * Schema update class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', [$this, 'updateSchema']);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Update module table schema
     *
     * @param Event $e
     *
     * @return bool
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');
        $updator       = new Schema\Updator110($this);
        $result        = $updator->upgrade($moduleVersion);

        return $result;
    }
}
