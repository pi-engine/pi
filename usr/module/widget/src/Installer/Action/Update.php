<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Installer\Action;

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
        $events->attach('update.post', [$this, 'updateBlock']);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Update block config specs and content meta
     *
     * @param Event $e
     * @return bool
     */
    public function updateBlock(Event $e)
    {
        $version = $e->getParam('version');
        if (version_compare($version, '2.0.0', '>=')) {
            return true;
        }

        $rowset = Pi::model('block_root')->select([
            'module'    => 'widget',
            'type <> ?' => 'script',
        ]);
        foreach ($rowset as $row) {
            $type        = $row->type ?: '';
            $row->config = Pi::api('block', 'widget')->getConfig($type);
            $row->save();
        }

        $update = [
            'content' => Pi::db()->expression(sprintf(
                'REPLACE(content, %s, %s)',
                '\'","desc":"\'',
                '\'","summary":"\''
            )),
        ];
        $where  = [
            'module' => 'widget',
            'type'   => ['list', 'media', 'carousel'],
        ];
        Pi::model('block')->update($update, $where);

        $update = [
            'meta' => Pi::db()->expression(sprintf(
                'REPLACE(meta, %s, %s)',
                '\'","desc":"\'',
                '\'","summary":"\''
            )),
        ];
        $where  = [
            'type' => ['list', 'media', 'carousel'],
        ];
        Pi::model('widget', 'widget')->update($update, $where);

        return true;
    }
}
