<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace   Module\Widget\Installer\Action;

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
        $events->attach('update.post', array($this, 'updateBlockConfig'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Update block config specs
     *
     * @param Event $e
     * @return bool
     */
    public function updateBlockConfig(Event $e)
    {
        $version = $e->getParam('version');
        if (version_compare($version, '2.0.0', '>')) {
            return true;
        }

        $rowset = Pi::model('block_root')->select(array(
            'module'    => 'widget',
            'type <> ?' => 'script',
        ));
        foreach ($rowset as $row) {
            $type = $row->type ?: '';
            $row->config = Pi::api('block', 'widget')->getConfig($type);
            $row->save();
        }

        return true;
    }
}
