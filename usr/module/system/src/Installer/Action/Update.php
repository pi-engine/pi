<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Installer\Action;

use Module\System\Installer\Schema;
use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Laminas\EventManager\Event;

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
        $events->attach('update.pre', [$this, 'updateSchema']);
        $events->attach('update.post', [$this, 'updateLog']);
        $events->attach('update.post', [$this, 'updateUserData']);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Logging
     *
     * @param Event $e
     */
    public function updateLog(Event $e)
    {
        $model = Pi::model('update', $this->module);
        $data  = [
            'title'   => _a('System updated'),
            'content' => _a('The system is updated successfully.'),
            'uri'     => Pi::url('www', true),
            'time'    => time(),
        ];
        $model->insert($data);
    }

    /**
     * Flush user data
     *
     * @param Event $e
     */
    public function updateUserData(Event $e)
    {
        Pi::user()->data()->gc();
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
        $updator       = new Schema\Updator361($this);
        $result        = $updator->upgrade($moduleVersion);

        return $result;
    }
}
